<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Desacopla la facturación de la API externa Factus.
 *
 * Renombra los artefactos con nombres neutros (`external_id`, `billing_*`,
 * `billing_logs`) y elimina toda estructura exclusiva de la integración remota
 * (credenciales de compañía y tokens OAuth).
 *
 * Es incremental e idempotente: preserva los datos existentes y funciona tanto
 * sobre bases ya migradas como en instalaciones nuevas.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->renameBillingLogsTable();
        $this->renameBillingLogsArtifacts(false);
        $this->renameInvoiceColumns();
        $this->renameNumberingRangeColumns();
        $this->dropCompanyFactusColumns();
        $this->dropFactusTokens();
    }

    public function down(): void
    {
        $this->renameColumnSafely('invoices', 'external_id', 'factus_id');
        $this->renameColumnSafely('invoices', 'billing_payload', 'factus_payload');
        $this->renameColumnSafely('invoices', 'billing_response', 'factus_response');
        $this->renameColumnSafely('numbering_ranges', 'external_id', 'factus_id');

        if (Schema::hasTable('billing_logs') && ! Schema::hasTable('factus_logs')) {
            Schema::rename('billing_logs', 'factus_logs');
        }

        $this->renameBillingLogsArtifacts(true);
        $this->restoreCompanyFactusColumns();
        $this->restoreFactusTokensTable();
    }

    private function renameBillingLogsTable(): void
    {
        if (Schema::hasTable('factus_logs') && ! Schema::hasTable('billing_logs')) {
            Schema::rename('factus_logs', 'billing_logs');
        }
    }

    /**
     * Renombra los índices y constraints heredados de `factus_logs`.
     *
     * Solo aplica en PostgreSQL (SQLite gestiona los nombres de forma anónima)
     * y es tolerante a fallos para no romper instalaciones ya normalizadas.
     */
    private function renameBillingLogsArtifacts(bool $restore): void
    {
        if (Schema::getConnection()->getDriverName() !== 'pgsql') {
            return;
        }

        $indexes = [
            'factus_logs_company_id_status_index' => 'billing_logs_company_id_status_index',
            'factus_logs_loggable_type_index' => 'billing_logs_loggable_type_index',
            'factus_logs_loggable_type_loggable_id_index' => 'billing_logs_loggable_type_loggable_id_index',
        ];

        $constraints = [
            'factus_logs_company_id_foreign' => 'billing_logs_company_id_foreign',
            'factus_logs_pkey' => 'billing_logs_pkey',
        ];

        $table = $restore ? 'factus_logs' : 'billing_logs';

        if (! Schema::hasTable($table)) {
            return;
        }

        foreach ($indexes as $factus => $neutral) {
            [$from, $to] = $restore ? [$neutral, $factus] : [$factus, $neutral];

            try {
                DB::statement("ALTER INDEX IF EXISTS {$from} RENAME TO {$to}");
            } catch (Throwable) {
                // El índice ya tiene el nombre esperado o no existe.
            }
        }

        foreach ($constraints as $factus => $neutral) {
            [$from, $to] = $restore ? [$neutral, $factus] : [$factus, $neutral];

            try {
                DB::statement("ALTER TABLE {$table} RENAME CONSTRAINT {$from} TO {$to}");
            } catch (Throwable) {
                // El constraint ya tiene el nombre esperado o no existe.
            }
        }
    }

    private function renameInvoiceColumns(): void
    {
        $this->renameColumnSafely('invoices', 'factus_id', 'external_id');
        $this->renameColumnSafely('invoices', 'factus_payload', 'billing_payload');
        $this->renameColumnSafely('invoices', 'factus_response', 'billing_response');
    }

    private function renameNumberingRangeColumns(): void
    {
        $this->renameColumnSafely('numbering_ranges', 'factus_id', 'external_id');
    }

    private function dropCompanyFactusColumns(): void
    {
        $columns = ['factus_client_id', 'factus_client_secret', 'factus_sandbox'];

        $existing = array_filter(
            $columns,
            static fn (string $column): bool => Schema::hasColumn('companies', $column),
        );

        if ($existing === []) {
            return;
        }

        Schema::table('companies', function (Blueprint $table) use ($existing): void {
            $table->dropColumn(array_values($existing));
        });
    }

    private function dropFactusTokens(): void
    {
        Schema::dropIfExists('factus_tokens');
    }

    private function restoreCompanyFactusColumns(): void
    {
        Schema::table('companies', function (Blueprint $table): void {
            if (! Schema::hasColumn('companies', 'factus_client_id')) {
                $table->string('factus_client_id')->nullable();
            }

            if (! Schema::hasColumn('companies', 'factus_client_secret')) {
                $table->string('factus_client_secret')->nullable();
            }

            if (! Schema::hasColumn('companies', 'factus_sandbox')) {
                $table->boolean('factus_sandbox')->default(true);
            }
        });
    }

    private function restoreFactusTokensTable(): void
    {
        if (Schema::hasTable('factus_tokens')) {
            return;
        }

        Schema::create('factus_tokens', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->text('access_token');
            $table->text('refresh_token')->nullable();
            $table->string('token_type')->default('Bearer');
            $table->timestamp('expires_at');
            $table->string('scope')->nullable();
            $table->timestamps();
            $table->index(['company_id', 'expires_at']);
        });
    }

    /**
     * Renombra una columna solo si el origen existe y el destino no.
     */
    private function renameColumnSafely(string $table, string $from, string $to): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        if (! Schema::hasColumn($table, $from) || Schema::hasColumn($table, $to)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($from, $to): void {
            $blueprint->renameColumn($from, $to);
        });
    }
};

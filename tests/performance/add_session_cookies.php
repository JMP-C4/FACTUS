<?php

declare(strict_types=1);

require __DIR__.'/../../vendor/autoload.php';

$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$metaPath = __DIR__.'/perf-meta.json';
$meta = json_decode(file_get_contents($metaPath), true);

$cookieName = $meta['cookieName'];
$encrypter = app('encrypter');
$prefix = Illuminate\Cookie\CookieValuePrefix::create($cookieName, $encrypter->getKey());

$rows = DB::table('sessions')
    ->join('users', 'users.id', '=', 'sessions.user_id')
    ->whereNotNull('sessions.user_id')
    ->orderBy('sessions.id')
    ->select('sessions.id as sid', 'users.email')
    ->get();

if ($rows->count() < 2000) {
    fwrite(STDERR, "ABORT: solo {$rows->count()} sesiones con usuario en BD.\n");
    exit(1);
}

$meta['sessions'] = $rows->take(2000)->map(fn ($r) => [
    'id' => $r->sid,
    'email' => $r->email,
    'cookie' => $encrypter->encryptString($prefix.$r->sid),
])->values()->all();

file_put_contents($metaPath, json_encode($meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
printf("meta actualizado: sesiones con cookie cifrada=%d\n", count($meta['sessions']));

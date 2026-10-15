# Design Tokens

## Dirección visual

Neo-brutalismo profesional: contraste alto, personalidad fuerte y suficiente sobriedad para un sistema de operación diaria.

## Paleta sugerida

```css
:root {
  --color-bg: #f4f1ea;
  --color-surface: #fffdf8;
  --color-ink: #111111;
  --color-primary: #2563eb;
  --color-accent: #f59e0b;
  --color-success: #16a34a;
  --color-danger: #dc2626;
  --color-muted: #94a3b8;
}
```

## Tipografía

- Headings: `Space Grotesk`, `Archivo Black` o equivalente fuerte.
- Body: `Inter Tight` o `Public Sans`.
- Labels/CTAs: uppercase con `tracking-[0.28em]`.
- Jerarquía:
  - `display`: `text-4xl md:text-5xl font-black uppercase`
  - `h1-h3`: `font-extrabold uppercase`
  - `label`: `text-xs uppercase tracking-[0.28em]`

## Bordes y radios

- Bordes primarios: `border-[3px] border-black`
- Bordes secundarios: `border-2 border-black`
- Radios: preferir `rounded-none` o `rounded-sm` solo cuando mejore legibilidad

## Sombras duras

```text
shadow-brutal-blue: 8px 8px 0 0 var(--color-primary)
shadow-brutal-amber: 8px 8px 0 0 var(--color-accent)
shadow-brutal-black: 6px 6px 0 0 var(--color-ink)
```

## Tokens de componentes

- Botón primario: fondo claro, borde negro, sombra azul, hover con translate leve inverso.
- Input: fondo blanco, borde negro o azul según foco, placeholder sobrio.
- Tarjeta: superficie clara, borde grueso, padding amplio, sombra dura.
- Badge de estado: color sólido, borde negro, tipografía uppercase.

## Layout

- `container`: ancho máximo amplio con padding generoso.
- POS: grid `12 cols`, catálogo `7`, resumen `5`.
- Login: panel central o split-screen con foco visual en CTA.

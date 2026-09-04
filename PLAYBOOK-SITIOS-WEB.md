# Playbook — qué lleva un sitio Antetodo

Estándar interno, no de cliente: resume lo que ya se construyó y probó en la flota de sitios
(Vanteris, Radiantte, IMS Global, Contemplación, Salvia Blanca, Samsara, La Manada Feliz) para que
un sitio nuevo — o una página nueva en uno existente — arranque con esto ya resuelto en vez de
reinventarlo. Se actualiza cada vez que un sitio resuelve algo mejor de lo que dice aquí; entonces
este documento se corrige, no el sitio nuevo se aparta del criterio.

No es checklist de lanzamiento del cliente — es la base técnica común. Cada sitio tiene además su
propio README y sus propias reglas de negocio (ver `CLAUDE.md` o `README.md` del repo).

## 1 · Fuente de verdad y generación

- **El contenido vive en datos, no en HTML.** `data/*.json` (o equivalente) es lo que se edita; el
  HTML se **genera** con un script (`_tools/build.js` o similar) y nunca se toca a mano. Un sitio
  bilingüe genera ES y EN desde el mismo dato, no desde dos plantillas separadas.
- **Nunca inventar un dato.** Un campo sin confirmar sale como marcador visible
  (`[falta: teléfono]`), nunca con un valor plausible. Precios, certificaciones, cifras de mercado:
  si no está verificado por el cliente, no se publica.
- **Fail-loud sobre fail-silent.** Si al build le falta un dato que no está registrado como
  pendiente, se detiene con un mensaje explícito — no publica algo a medias sin avisar.
- Un elemento sin archivo real (foto, PDF, ficha) muestra "próximamente" o el estado que sea,
  nunca un enlace roto ni un placeholder que aparente ser contenido final.

## 2 · Internacionalización (ES/EN)

- Estructura `/en/` en espejo de la raíz, generada del mismo dato fuente — nunca traducción manual
  de HTML aparte que se puede desincronizar.
- `hreflang` ES/EN/x-default en cada par de páginas, en ambos sentidos.
- Selector de idioma visible en el header, y en contenido tipo landing/blog también dentro de la
  página.
- Nombres propios (marcas, vinos, platillos con nombre de autor) no se traducen; el resto sí, de
  forma fiel — nada añadido ni inventado en la traducción.

## 3 · SEO técnico

- **Canonical + slash final consistente.** Si el `.htaccess` redirige `/ruta` → `/ruta/`, el
  canonical, el `og:url`, el hreflang y el propio sitemap tienen que apuntar YA a la versión con
  slash — nunca a una URL que redirige.
- **URLs limpias**, sin `.html` visible, con 301 desde las rutas viejas — nunca se rompe una URL
  indexada sin redirigir.
- `sitemap.xml` real (solo páginas indexables; noindex fuera), `robots.txt` que permite
  explícitamente los bots de IA (GPTBot, ClaudeBot, PerplexityBot, etc.) y `llms.txt` con un
  resumen legible por IA del sitio — productos/servicios reales, no genérico.
- Enlaces internos verificados en cada build (`check-enlaces` o equivalente): cero rotos antes de
  publicar.

## 4 · GEO — schema.org / JSON-LD

- Cada tipo de página lleva el schema que le corresponde, no solo home y producto:
  `CollectionPage` + `ItemList` en listados/categorías, `FAQPage` en preguntas frecuentes (las
  mismas preguntas que ve el usuario, no un set paralelo), `BreadcrumbList` en todo lo que no sea
  home, `Organization`/`WebSite` con `SearchAction` si hay buscador interno.
- El schema declara solo lo que es real — un `contactPoint` o `address` no se emite si el dato
  está en `null`.
- Verificar JSON-LD válido en el 100% de las páginas antes de publicar, no solo en las principales.

## 5 · Performance

- Imágenes a WebP, redimensionadas al tamaño real de despliegue (no servir un original de varios
  MB para mostrarlo en 400px). Es la ganancia más barata y la primera que se hace.
- LCP: la imagen o video de hero se sirve con `preload` + `fetchpriority="high"`, con versión
  responsiva por breakpoint — nunca un solo asset pesado para todos los tamaños de pantalla.
- Video de fondo: no se descarga en móvil ni con `save-data`/`prefers-reduced-motion` activos;
  streaming en vez de blob completo en escritorio.
- Fuentes (Typekit/Google Fonts y similares) con `preload` + `swap`: el texto sale con la fuente de
  respaldo primero, nunca en blanco esperando la fuente.
- Analítica pesada (GA4, píxeles) diferida a la primera interacción o a ~3s, con un stub que
  encola los eventos tempranos para no perder medición.
- Cache-busting (`?v=`) en CSS/JS para que un deploy no sirva assets viejos cacheados.

## 6 · Accesibilidad

- `role="main"` (o `<main>` real) en el contenido principal de cada plantilla.
- `aria-label` en botones/íconos sin texto visible (WhatsApp, redes, buscador, back-to-top).
- Contraste verificado contra el peor caso real (texto sobre foto con velo), no solo sobre el
  fondo plano — AA (4.5:1) como mínimo.
- `alt` en toda imagen con significado; inputs de formulario con `label` asociado.

## 7 · Analítica y atribución

- GTM o GA4 + píxel de Meta sitewide, con evento de contacto (clic en WhatsApp/llamar/agendar) para
  poder optimizar pauta a esa acción, no solo a pageview.
- Si el sitio manda tráfico a un motor de reservas/checkout en OTRO dominio: los `utm_*`/`fbclid`/
  `gclid` se guardan en `sessionStorage` y se reinyectan en el enlace de salida (con
  `MutationObserver` si el widget externo inyecta el enlace después) — si no, la campaña pierde la
  atribución en el salto de dominio.

## 8 · Contacto y captura de datos

- Un solo correo/canal de contacto público por decisión del cliente, no varios que se puedan
  desincronizar — y confirmarlo explícitamente con el cliente, no asumirlo.
- Formulario de captura: honeypot + límite de envíos por IP/hora como mínimo. Nunca exponer rutas
  de archivo directamente descargables si primero deben pasar por un formulario — servir por script
  que valida contra el catálogo real, no por URL adivinable.
- Aviso de privacidad enlazado desde footer y desde el propio formulario; sin razón social
  confirmada, sale con marcador de pendiente y no se promueve activamente la captura.

## 9 · Estructura del repo (convención)

```
data/            fuente de verdad — JSON editable, nunca el HTML
_tools/          generador(es) + scripts idempotentes (add-main.js, check-enlaces, etc.)
assets/ (o css/js/img/fonts/)  estáticos
<generado>       HTML de salida + sitemap.xml, robots.txt, llms.txt
README.md        cómo correr el build, qué es fuente de verdad, estado y pendientes
```

## 10 · Antes de dar por lanzado un sitio

- [ ] `indexable`/`noindex` correcto para la fase en la que está (no indexar antes de tiempo).
- [ ] Sitemap solo con páginas reales y listas; nada en `pending_review` dentro de él.
- [ ] Cero enlaces internos rotos, cero imágenes rotas.
- [ ] JSON-LD válido en el 100% de páginas.
- [ ] `hreflang` en ambos sentidos si hay bilingüe.
- [ ] Todo dato pendiente está listado explícitamente, ninguno inventado.
- [ ] Analítica y atribución cross-domain probadas si aplica.

---

*Este documento es común a la flota de sitios de Antetodo Marketing — se corrige aquí cuando un
sitio resuelve algo mejor, no se bifurca por repo.*

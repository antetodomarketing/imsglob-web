# Playbook — qué lleva un sitio Antetodo

Estándar interno, no de cliente: resume lo que ya se construyó y probó en la flota de sitios
(Vanteris, Radiantte, IMS Global, Contemplación, Salvia Blanca, Samsara, La Manada Feliz) para que
un sitio nuevo — o una página nueva en uno existente — arranque con esto ya resuelto en vez de
reinventarlo. Se actualiza cada vez que un sitio resuelve algo mejor de lo que dice aquí; entonces
este documento se corrige, no el sitio nuevo se aparta del criterio.

No es checklist de lanzamiento del cliente — es la base técnica común. Cada sitio tiene además su
propio README y sus propias reglas de negocio (ver `CLAUDE.md` o `README.md` del repo).

**Este archivo vive duplicado en los 7 repos de la flota a propósito** (cada sitio lo trae consigo,
sin depender de otro repo para consultarlo) — pero eso solo funciona si cada copia está fusionada a
`main`. Ver la nota en la sección 11: al 26-sep-2026, tres revisiones programadas después de
detectado el incidente del 19-sep, las 7 copias **seguían** sin fusionar a `main` en ningún repo —
el documento sigue sin cumplir su propósito.

## 1 · Fuente de verdad y generación

- **El contenido vive en datos, no en HTML.** `data/*.json` (o equivalente) es lo que se edita; el
  HTML se **genera** con un script (`_tools/build.js` o similar) y nunca se toca a mano. Un sitio
  bilingüe genera ES y EN desde el mismo dato, no desde dos plantillas separadas.
- **Nunca inventar un dato.** Un campo sin confirmar sale como marcador visible
  (`[falta: teléfono]`), nunca con un valor plausible. Precios, certificaciones, cifras de mercado:
  si no está verificado por el cliente, no se publica.
- **Cada afirmación de contenido lleva su fuente.** En páginas de solución/técnicas, cada dato
  factual (por qué importa un borde sellado, por qué falla algo, una spec) va con un campo
  `_fuente` que dice de dónde sale. Nunca se afirma sobre el cliente algo que no consta —
  certificaciones, años de experiencia, cifras de desempeño — aunque suene bien.
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
- Un helper resuelve la ruta con prefijo de idioma (`/en/...`); nunca se concatena el prefijo a
  mano en cada botón — así no se cuelan enlaces a rutas que no existen en el otro idioma
  (ej. `/en/descargar/` cuando la página real es `/en/download/`).

## 3 · SEO técnico

- **Canonical + slash final consistente.** Si el `.htaccess` redirige `/ruta` → `/ruta/`, el
  canonical, el `og:url`, el hreflang y el propio sitemap tienen que apuntar YA a la versión con
  slash — nunca a una URL que redirige.
- **URLs limpias**, sin `.html` visible, con 301 desde las rutas viejas — nunca se rompe una URL
  indexada sin redirigir.
- `sitemap.xml` real (solo páginas indexables; noindex fuera), `robots.txt` que permite
  explícitamente los bots de IA (GPTBot, ClaudeBot, PerplexityBot, etc.) y `llms.txt` con un
  resumen legible por IA del sitio — productos/servicios reales, no genérico.
- Meta description sale de la entradilla real de la página, no de repetir el nombre; se recorta
  por palabra completa, nunca a media sílaba.
- Enlaces internos verificados en cada build (`check-enlaces` o equivalente): cero rotos antes de
  publicar.
- Páginas de categoría/solución con contenido real (&gt;150 palabras propias) — una página con solo
  título y tarjetas es una página vacía para el buscador.

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
  encola los eventos tempranos para no perder medición — **excepto** los clics de conversión
  directa (WhatsApp, llamar, agendar): esos se marcan de inmediato con un listener inline en el
  `<head>` (antes de que cargue el bundle diferido), porque abren en pestaña nueva y un toque
  antes del primer scroll no se cuenta si el rastreo vive solo en el script diferido.
- Cache-busting (`?v=`) en CSS/JS para que un deploy no sirva assets viejos cacheados.
- `deploy`/CI excluye explícitamente directorios de build obsoletos (`dist-*/` viejos, carpetas de
  prueba) — un bug de exclusión mal escrito puede publicar una versión vieja encima de la nueva.

## 6 · Accesibilidad

- `role="main"` (o `<main>` real) en el contenido principal de cada plantilla.
- `aria-label` en botones/íconos sin texto visible (WhatsApp, redes, buscador, back-to-top).
- Contraste verificado contra el peor caso real (texto sobre foto con velo), no solo sobre el
  fondo plano — AA (4.5:1) como mínimo, con la cifra medida (no estimada) dejada en un comentario
  del CSS.
- `alt` en toda imagen con significado; inputs de formulario con `label` asociado.

## 7 · Analítica y atribución

- GTM o GA4 + píxel de Meta sitewide, con evento de contacto (clic en WhatsApp/llamar/agendar) para
  poder optimizar pauta a esa acción, no solo a pageview.
- Si el sitio manda tráfico a un motor de reservas/checkout en OTRO dominio: los `utm_*`/`fbclid`/
  `gclid` se guardan en `sessionStorage` y se reinyectan en el enlace de salida (con
  `MutationObserver` si el widget externo inyecta el enlace después) — si no, la campaña pierde la
  atribución en el salto de dominio.
- Un GTM con contenedor placeholder (`GTM-XXXXXXX`) se marca explícitamente como pendiente en el
  README del sitio — no se deja como si ya midiera. Si el placeholder ya está en producción y da
  404 en cada carga, se **retira** de inmediato (sin medición) en vez de dejarlo fallando — así
  quedó resuelto en `imsglob-web`.

## 8 · Contacto y captura de datos

- Un solo correo/canal de contacto público por decisión del cliente, no varios que se puedan
  desincronizar — y confirmarlo explícitamente con el cliente, no asumirlo.
- Formulario de captura: honeypot + límite de envíos por IP/hora como mínimo. Nunca exponer rutas
  de archivo directamente descargables si primero deben pasar por un formulario — servir por script
  que valida el identificador (SKU, folio) contra el catálogo real, nunca una ruta adivinable desde
  el navegador.
- El contenido público (la ficha en línea) no se esconde detrás del formulario — solo el archivo
  descargable (PDF). El SEO/GEO vive del contenido público; el formulario es fricción solo para el
  activo, no para la información.
- La descarga arranca de inmediato tras el formulario; cualquier correo de seguimiento (con enlace
  firmado HMAC para no volver a pedir datos) es cortesía, no trámite bloqueante.
- Aviso de privacidad enlazado desde footer y desde el propio formulario; sin razón social
  confirmada, sale con marcador de pendiente y no se promueve activamente la captura.

## 9 · Fotografía e imagen de marca

- **Catálogo de producto**: mismo fondo, misma luz, mismo encuadre en todo el set — una foto de
  estudio dispareja rompe la coherencia aunque cada imagen individual se vea bien.
- Recorte automatizado por script, no a mano imagen por imagen: detecta si el contenido es un
  objeto aislado (se recorta a la caja del objeto, con aire porcentual fijo) o una escena completa
  (sticky mats, cuartos: se recorta cuadrado al centro) — un mismo criterio, aplicado por código,
  para que todo el lote quede consistente.
- **Favicon**: se extrae SOLO el isotipo/ícono de la marca, nunca el logo completo con wordmark —
  a 16-32px un lockup horizontal es una mancha ilegible. El isotipo se ajusta al ancho/alto del
  lienzo (no lo dejes flotando con aire de sobra en un canvas pensado para un logo completo).
- **Preview de enlace (`og:image`)**: es una imagen propia de 1200×630, nunca el logo horizontal
  recortado a cuadrado — el recorte automático de WhatsApp/redes puede mostrar un fragmento del
  isotipo que se lee como otra letra. Declarar `og:image:width`/`height` y `twitter:image`.

## 10 · Contenido: exactitud y vigencia

- Antes de un go-live (o de cualquier cambio de alcance del negocio), barrer TODO el sitio —
  fuentes de datos, páginas generadas, landings de campaña y sus copias anidadas — por menciones a
  personal que ya no está, ofertas/paquetes descontinuados o reformulados. Un solo lugar sin
  actualizar dentro de un site.json ya corregido reintroduce el dato viejo en el próximo build.
- Disclaimers legales/médicos estandarizados (un solo texto, no uno distinto por página) en todo
  contenido de blog/notas que toque temas de salud o resultados.
- Precios y datos operativos no confirmados se publican marcados como **PENDIENTE de confirmar**,
  nunca aproximados en silencio.

## 11 · Hosting y despliegue

- Pila estándar: Hostinger (integración Git — push a `main` publica solo, sin FTP manual) +
  Sucuri como WAF/CDN delante. Si el cliente ya tenía otro hosting (GoDaddy, cPanel), migrar a este
  patrón en vez de mantener un despliegue por FTP ad hoc.
- El origen real para Sucuri (IP de Hostinger) se documenta en el README del repo — necesario para
  que el firewall no bloquee el propio servidor.
- Un solo canal de despliegue vigente. Si se migra de proveedor, el workflow viejo (p. ej. un
  GitHub Action de FTP) se **elimina el mismo día** que se completa la migración — no se deja
  fallando en rojo en el historial de Actions mientras tanto.
- Si el despliegue vive fuera de GitHub Actions (integración directa del hosting, como Hostinger),
  el README documenta explícitamente **cómo se verifica que un push llegó a producción** — de lo
  contrario un deploy roto no se detecta hasta que alguien visita el sitio en vivo.
- Sin gate de PR: esta flota empuja directo a `main` (0 PRs abiertos/cerrados en el historial de
  los repos, con un solo operador). Si un sitio SÍ requiere revisión antes de producción, decirlo
  explícito en su README — no asumir que todos los repos funcionan igual. Ver sección 14.
- **Higiene de ramas**: una rama de trabajo que ya se integró (a mano o por otra vía) se borra. Una
  rama vieja sin PR y sin borrar se ve, meses después, como trabajo pendiente cuando ya no lo es —
  cuesta una revisión completa para confirmar que está muerta.
  > **Caso real, 19-sep-2026:** este mismo archivo se creó el 4 de septiembre en una rama de
  > trabajo en cada uno de los 7 repos de la flota (y se refinó el 11 de septiembre en una segunda
  > rama solo en `vanteris-web`) — y las 7 copias seguían sin fusionar a `main` dos semanas
  > después, sin ningún Pull Request abierto para ellas. Es el ejemplo perfecto de la regla de
  > arriba: nadie iba a notar que faltaba hasta auditar rama por rama. Corregido el 19-sep con esta
  > actualización, empujada a la rama de trabajo vigente de cada repo — queda pendiente que alguien
  > con acceso de escritura la fusione a `main` en cada uno.
  >
  > **Seguimiento, 26-sep-2026:** tercera revisión programada consecutiva (04-sep, 19-sep, 26-sep)
  > que encuentra este archivo solo en ramas de trabajo nuevas, nunca fusionado a `main` en ninguno
  > de los 7 repos. Cada corrida abre una rama más en vez de resolver la de la corrida anterior —
  > los 7 repos acumulan ya 2-3 ramas `claude/*` sin PR y sin borrar cada uno, viejas de semanas.
  > La higiene de ramas que este documento pide no se está aplicando a sí mismo, y ninguna revisión
  > automatizada puede corregirlo sola: fusionar y limpiar requiere una decisión y una acción de
  > alguien con acceso de escritura en cada repo. Acción pendiente real, sin resolver desde hace
  > 22 días: fusionar la copia vigente de este archivo a `main` en los 7 repos y borrar las ramas
  > `claude/*` ya integradas o abandonadas.

## 12 · Estructura del repo (convención)

```
data/            fuente de verdad — JSON editable, nunca el HTML
_tools/          generador(es) + scripts idempotentes (add-main.js, check-enlaces, etc.)
assets/ (o css/js/img/fonts/)  estáticos
<generado>       HTML de salida + sitemap.xml, robots.txt, llms.txt
README.md        cómo correr el build, qué es fuente de verdad, estado y pendientes
```

## 13 · Antes de dar por lanzado un sitio

- [ ] `indexable`/`noindex` correcto para la fase en la que está (no indexar antes de tiempo).
- [ ] Sitemap solo con páginas reales y listas; nada en `pending_review` dentro de él.
- [ ] Cero enlaces internos rotos, cero imágenes rotas.
- [ ] JSON-LD válido en el 100% de páginas.
- [ ] `hreflang` en ambos sentidos si hay bilingüe.
- [ ] Todo dato pendiente está listado explícitamente, ninguno inventado.
- [ ] Barrido de menciones vigentes (personal, ofertas) — nada que quedó de una versión anterior
      del negocio.
- [ ] Favicon = isotipo solo; `og:image` propio, no el logo recortado.
- [ ] Analítica y atribución cross-domain probadas si aplica; eventos de conversión directa
      confirmados desde el primer clic.
- [ ] IDs reales de GA4 / Meta Pixel / GTM cargados — ningún placeholder en producción.
- [ ] Ramas de trabajo ya integradas, borradas.

## 14 · Proceso de revisión

- Cambios grandes (rediseño, migración de hosting, cambio de precios/legal) pasan por Pull Request
  con al menos una revisión, aunque sea de un minuto — el costo de revisar es menor que el de un
  cambio de precio o de hosting mal fusionado directo a `main`.
- Copy menor y ajustes de contenido pueden seguir yendo directo a la rama principal mientras la
  flota la opere una sola persona.
- En cuanto un segundo operador toque el mismo repo, este criterio deja de sostenerse — es el
  momento de exigir PR para todo, no solo para cambios grandes.

---

*Este documento es común a la flota de sitios de Antetodo Marketing — se corrige aquí cuando un
sitio resuelve algo mejor, no se bifurca por repo.*

*Última actualización: 26 de septiembre de 2026. Consolida la versión del 19-sep (hosting sin CI
visible, incidente de higiene de ramas) con el seguimiento del 26-sep: el incidente de ramas sigue
sin resolverse, ahora en su tercera revisión consecutiva. Patrones observados hasta esa fecha en
Vanteris, Radiantte, IMS Global, Contemplación, Salvia Blanca, Samsara y La Manada Feliz.*

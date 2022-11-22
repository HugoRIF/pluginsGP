=== Gameplanet Login ===
Contributors: (esto debe ser una lista de wordpress.org userid's)
Donate link: http://example.com/
Tags: comments, spam
Requires at least: 3.0.1
Tested up to: 3.4
Stable tag: 4.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Aquí va una descripción corta. Esto no debe ser mayor a 150 caracteres. Sin Markdown. (https://www.markdownguide.org/cheat-sheet/)

== Descripción ==

Esta es la descripción larga. No tiene límite y puedes usar Markdown (al igual que en las siguientes secciones).

Para retrocompatibilidad, si falta esta sección, será utilizada toda la descripción corta.

Unas notas de las secciones de arriba:

*   "Contributors" es una lista separada por comas de nombre de usuarios de wp.org/wp-plugins.org
*   "Tags" es una lista separada por comas de las etiqutas que apliquen al plugin
*   "Requires at least" es la versión más baja en la que el plugin funcionaría
*   "Tested up to" is the highest version that you've *successfully used to test the plugin*. Note that it might work on
*   "Tested up to" es la versión en la que "probaste el plugin con éxito". Nota: que podría funcionar en
versiones más altas... esto es solo la versión más alta que haz verificado.
*   "Stable tag" debe indicar la Subversión "tag" de la versión más estable, o "trunk," si usas `/trunk/` para estable.

    Note that the `readme.txt` of the stable tag is the one that is considered the defining one for the plugin, so
if the `/trunk/readme.txt` file says that the stable tag is `4.3`, then it is `/tags/4.3/readme.txt` that'll be used
for displaying information about the plugin.  In this situation, the only thing considered from the trunk `readme.txt`
is the stable tag pointer.  Thus, if you develop in trunk, you can update the trunk `readme.txt` to reflect changes in
your in-development version, without having that information incorrectly disclosed about the current stable version
that lacks those changes -- as long as the trunk's `readme.txt` points to the correct stable tag.

    Si no se provee con un tag estable, se asime que trunk es estable, pero deberías especificar "trunk" si es donde
pones tu versión estable, para evitar cualquier duda.

== Instalación ==

Esta sección describe cómo instalar el plugin y ponerlo a trabajar.

Ejemplo:

1. Sube `gameplanet.php` al directorio `/wp-content/plugins/`
1. Activa el plugin atravéz de 'Plugins' en el menpu de WordPress
1. Pon `<?php do_action('gameplanet_hook'); ?>` en tus temas

== Preguntas Frecuentes ==

= Una pregunta que álguien podría tener =

Una respuesta a esa pregunta.

= ¿ Y qué tal foo bar? =

También dilemas sobre foo bar.

== Capturas ==

1. Esta descripción corresponde a screenshot-1.(png|jpg|jpeg|gif). Nota que la captura se toma desde
el directorio /assets o del directorio que contenga el readme.txt estable (tags o trunk). Capturas en el directorio
directory take precedence. For example, `/assets/screenshot-1.png` would win over `/tags/4.3/screenshot-1.png`
/assets prevalecerán. Por ejemplo, `/assets/screenshot-1.png` ganaría sobre `/tags/4.3/screenshot-1.png`
(o jpg, jpeg, gif).
2. Esto es la segunda captura

== Changelog ==

= 1.0 =
* un cambio desde la versión anterior.
* Otro cambio.

= 0.5 =
* Enlista las versiones del más reciente hasta el más viejo.

== Noticia de actualización ==

= 1.0 =
Describe la razón por la que debes actualizar. No más de 300 caracteres.

= 0.5 =
Esta versión arregla un bug. Actualiza inmediatamente.

== Sección arbitraria ==

Puedes proveer con secciones arbitrarias, de la misma forma que las secciones de arriba. Esto puede ser usado para plugins
complicados donde se necesite más información que no entre dentro de "Descripción" o 
"installation."  Arbitrary sections will be shown below the built-in sections outlined above.
"instalación". Estas secciones aparecerán debajo de las secciones definidas arriba.

== Ejemplo rápido de Markdown ==

Lista ordenada:

1. Algo
1. Algo más
1. Aún más cosas sobre el plugin

Lista sin orden:

* algo
* algo más
* algo más más

Aquí hay un link para [WordPress](http://wordpress.org/ "Tu software favorito") y uno para [Markdown's Syntax Documentation][markdown syntax].
Los títulos son opcionales.

[markdown syntax]: http://daringfireball.net/projects/markdown/syntax
            "Markdown is what the parser uses to process much of the readme file"

Markdown uses email style notation for blockquotes and I've been told:
> Asterisks for *emphasis*. Double it up  for **strong**.

`<?php code(); // goes in backticks ?>`
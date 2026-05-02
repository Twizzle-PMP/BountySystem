# BountySystem

Plugin de sistema de recompensas para PocketMine-MP 5. Permite que los jugadores coloquen dinero sobre la cabeza de otros jugadores. Cuando alguien mata al objetivo, recibe la recompensa automáticamente.

## Qué hace

El plugin maneja un sistema completo de bounties. Los jugadores pueden usar comandos o menús GUI para poner dinero como recompensa en otro jugador. Cuando ese jugador muere asesinado, quien lo mató recibe el dinero. El sistema registra todo el historial, soporta cancelación de bounties con reembolso, y las recompensas expiran después de ciertos días si no se cobran.

## Instalación

1. Descarga el archivo ZIP del plugin
2. Extrae la carpeta BountySystem en tu carpeta plugins/
3. Necesitas tener instalados estos plugins primero:
   - EconomyAPI (para el sistema de dinero)
   - InvMenu (para los menús de inventario)
4. Reinicia tu servidor
5. Abre plugins/BountySystem/config.yml y configura los valores que quieras cambiar

## Comandos disponibles

El comando principal es /bounty. Desde ahí puedes hacer:

/bounty
Abre el menú principal con las opciones del sistema.

/bounty place <jugador> <cantidad>
Coloca una recompensa en un jugador. Necesitas tener el dinero en tu cuenta.

/bounty cancel <jugador>
Cancela una recompensa que pusiste y recuperas el dinero. Los administradores pueden cancelar cualquier recompensa.

/bounty list
Muestra una lista de todas las recompensas activas en formato GUI.

/bounty history
Muestra el historial de recompensas que han sido cobradas. Incluye quién la puso, quién la cobró y cuándo.

/bounty top
Muestra las 10 recompensas más altas en el chat.

## Permisos

El sistema usa dos permisos principales:

bountysystem.use
Permite que los jugadores usen el sistema de bounties. Por defecto todos lo tienen.

bountysystem.admin
Permite a los administradores cancelar cualquier recompensa. Por defecto solo los que son OP.

## Configuración

El archivo config.yml tiene todas las opciones ajustables. Abrelo con un editor de texto:

settings:
  min-bounty: 100
La cantidad mínima que se puede colocar como recompensa.

  max-bounty: 100000
La cantidad máxima de dinero que se puede apostar.

  currency-symbol: "$"
El símbolo que se muestra para el dinero.

  allow-self-bounty: false
Si está en false, los jugadores no pueden colocar recompensa sobre sí mismos.

  bounty-expire-days: 7
Los bounties se eliminan automáticamente después de X días sin ser cobrados.

  history-limit: 50
Cuántas entradas del historial guardar en la base de datos.

messages:
Aquí están todos los mensajes que ven los jugadores. Puedes personalizarlos completamente. Los códigos con & controlan los colores: &a = verde, &c = rojo, &e = amarillo, &6 = oro, &b = cyan, etc.

sounds:
Los sonidos que se reproducen cuando ocurren eventos. Los valores son sonidos de Minecraft.

## Cómo funciona

Cuando un jugador usa /bounty place nombreJugador 500, se le descuenta 500 del dinero de su cuenta. La recompensa queda activa inmediatamente. Ese jugador recibe una notificación de que tiene una recompensa.

Si alguien mata al jugador que tiene recompensa, el sistema detecta automáticamente la muerte, verifica si tiene bounty activo, y transfiere el dinero al asesino. El evento se registra en el historial con la fecha y hora exacta. El servidor anuncia a todos que se cobró la recompensa.

Si quieres aumentar una recompensa que ya existe, solo pon otra sobre el mismo jugador. Los montos se suman. Si quieres cancelar, usa /bounty cancel nombreJugador y recuperas tu dinero.

Las recompensas que no se cobran se eliminan automáticamente después de los días configurados.

## Almacenamiento de datos

Todo se guarda en plugins/BountySystem/data/ en archivos JSON:

bounties.json contiene las recompensas activas en este momento.

history.json contiene el registro de todas las recompensas cobradas con información completa de cada una.

Los datos se guardan automáticamente cada vez que ocurre un evento importante. Si el servidor se reinicia, todas las recompensas activas persisten.

## Estructura del plugin

BountySystem/
├── plugin.yml
├── config.yml
├── README.md
└── src/Twizzle/BountySystem/
    ├── Loader.php
    ├── command/BountyCommand.php
    ├── gui/BountyMainMenu.php
    ├── gui/BountyListMenu.php
    ├── gui/BountyHistoryMenu.php
    ├── manager/BountyManager.php
    └── data/BountyData.php

Loader.php es la clase principal que controla todo. Los comandos están en BountyCommand.php. Los menús visuales están en las tres clases gui. BountyManager.php maneja la lógica de guardar, cargar y procesar bounties. BountyData.php es el modelo de datos.

## Detalles técnicos

El plugin funciona con PocketMine-MP 5.0.0 en adelante. Depende de EconomyAPI para manejar dinero y de InvMenu para los menús. Usa el espacio de nombres Twizzle\BountySystem.

Escucha el evento PlayerDeathEvent para detectar muertes y cobrar bounties automáticamente. Cada 10 minutos ejecuta una tarea que revisa si hay bounties expirados y los elimina.

## Solucionar problemas

Si el plugin no inicia, verifica que tengas EconomyAPI e InvMenu instalados. Revisa la consola del servidor para mensajes de error sobre dependencias faltantes.

Si los menús GUI no aparecen, asegúrate de que InvMenu esté habilitado correctamente y que el jugador tenga permiso bountysystem.use.

Si el dinero no se descuenta, comprueba que EconomyAPI funcione usando /money en el juego. Verifica que el jugador tenga saldo suficiente. Mira la consola para errores de EconomyAPI.

Si los bounties no se cobran cuando matan al objetivo, confirma que el jugador muerto tenía una recompensa activa. Verifica que el asesino sea un jugador válido. Asegúrate de que EconomyAPI pueda procesar el pago.

## Créditos

Creado por Twizzle para redes profesionales de PocketMine-MP.

## Licencia

Este plugin se proporciona tal cual para usar en servidores de PocketMine-MP.

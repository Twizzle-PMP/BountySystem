# BountySystem

Plugin de bounties para PocketMine-MP 5.

## Qué hace

Los jugadores ponen dinero en la cabeza de otros. Si alguien mata al objetivo, se lleva la plata. Eso es todo.

## Instalación

1. Mete la carpeta BountySystem en plugins/
2. Necesitas EconomyAPI
3. Reinicia el server
4. Configura config.yml si quieres cambiar valores

## Comandos

`/bounty` - Abre el menú
`/bounty place <jugador> <dinero>` - Pone recompensa
`/bounty cancel <jugador>` - Cancela y recupera dinero
`/bounty list` - Ve todas las bounties activas
`/bounty history` - Ve el historial
`/bounty top` - Ve las 10 más altas

## Config

En config.yml puedes cambiar:
- Monto mínimo y máximo
- Símbolo de dinero
- Cuántos días expiran las bounties
- Los mensajes
- Los sonidos

## Cómo funciona

Un jugador hace `/bounty place nombre 500` y le descuentan 500. Si ese jugador muere, quien lo mató se lleva los 500. Si quiere cancelar la bounty, usa `/bounty cancel nombre` y recupera el dinero.

Las bounties se guardan en JSON, así que persisten entre reinicios.

## Permisos

`bountysystem.use` - Para usar el sistema (todos lo tienen por defecto)
`bountysystem.admin` - Para cancelar cualquier bounty (solo OP)

# Story 1.2: Keine Persistenz im Response-Listener

Als Admin
moechte ich eine stabile Tag-Verarbeitung ohne Schreibzugriffe im Response-Listener,
damit keine Race-Conditions auftreten.

## Acceptance Criteria

- Gegeben der Response-Listener `TagManagerListener::onKernelResponse`
- Wenn die Seite gerendert wird
- Dann werden keine Persistenzoperationen ausgefuehrt
- Und das automatische Deaktivieren abgelaufener Tags ist in einen Service/Job verlagert

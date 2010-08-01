Serwis pozwalający na dodawanie zdjęć/wpisów na Blip.pl via e-mail

Aby uruchomić własną instancję, należy:
  * zaimportować bazę danych z pliku _/eblip.sql_
  * podać dane do serwera bazy danych oraz serwera POP3 w pliku _/include/config.php_
  * stworzyć cron job wykonujący cyklicznie _/get_mail.php_ (ew. skrypt bash _/updater_)
  * dla obsługi powiadomień SMS, należy dopisać treść funkcji *sendSms* w pliku _/include/config.php_

W przyszłości miały być dodane następujące funkcje:
  * obsługa OAuth
  * nowa wersja UI w oparciu o [jQTouch](http://www.jqtouch.com/)

Jeśli ktoś chce dalej rozwijać eBlip-a, proszę o kontakt na *admin(maupka)suda.pl*

Źródło udostępnione na prośbę [^bobiko](http://bobiko.blip.pl/)
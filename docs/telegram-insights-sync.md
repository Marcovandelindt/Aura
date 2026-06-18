# Telegram sync voor Inzichten

## Plan

1. Maak een Telegram bot aan via BotFather (`/newbot`) — levert een bot token op
2. Stuur één berichtje naar je nieuwe bot zodat er een chat ID gegenereerd wordt
3. Zet `TELEGRAM_BOT_TOKEN` en `TELEGRAM_CHAT_ID` in de `.env`
4. Bouw een `TelegramService` die bij elk nieuw inzicht een berichtje stuurt
5. Per bestaand inzicht komt er een klein "stuur naar Telegram" knopje, zodat oude inzichten alsnog doorgestuurd kunnen worden
6. `InsightController` krijgt een `resend` action voor dat knopje

## Stappen voor de gebruiker

- Ga naar Telegram en zoek **@BotFather**
- Stuur `/newbot` en volg de stappen
- Kopieer het bot token dat je terugkrijgt
- Stuur één willekeurig berichtje naar je nieuwe bot
- Ga naar `https://api.telegram.org/bot{TOKEN}/getUpdates` om je chat ID op te halen
- Vul beide in in `.env`:

```
TELEGRAM_BOT_TOKEN=
TELEGRAM_CHAT_ID=
```

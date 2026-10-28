# ccs.com.az — Cost Control Services

Сайт компании Cost Control Services (контроль затрат для ресторанов и HoReCa, Баку).

## Этап 1 — прототипы главной (статичный HTML)

| Путь | Вариант |
|------|---------|
| `index.html` | Страница выбора варианта (временная, `noindex`) |
| `v1/` | Тёмный премиум — ближе всего к презентации |
| `v2/` | Светлый консалтинг — «чертёжная» сетка, пример P&L-отчёта, FAQ, карта |
| `v3/` | Смелая редакционная — крупная типографика, калькулятор фудкоста |

Каждая версия — `index.html` + `style.css` + `script.js`, без сборки и фреймворков.
Секции размечены комментариями `<!-- SECTION: ... -->` — по ним страница будет разбита на PHP-партиалы.

Общие файлы — `assets/img/`: логотип (SVG, восстановлен по презентации), логотипы 18 клиентов, 3D-рендеры и фото из презентации.

### Локальный просмотр

```bash
python -m http.server 8080
```

Затем открыть http://localhost:8080/.

### Деплой (cPanel → Git Version Control)

Репозиторий клонируется в `/home/ccscom/repositories/ccs.com.az`. При «Deploy HEAD Commit» cPanel выполняет `.cpanel.yml` — копирует `index.html`, `assets/`, `v1/`, `v2/`, `v3/`, `api/` в `/home/ccscom/public_html/`.
Порядок: Manage → Pull or Deploy → Update from Remote → Deploy HEAD Commit.

Сайт идёт через Cloudflare, который кэширует CSS/JS на несколько часов. Поэтому ссылки на них в HTML написаны как `style.css?v=dev`, а при деплое `.cpanel.yml` заменяет `dev` на метку времени — после каждого деплоя браузеры и Cloudflare сразу берут новые файлы. Новые CSS/JS подключать так же, с `?v=dev`.

## Instagram — лента последних публикаций

Внизу каждой версии — сетка из 12 последних постов @costcontrol.services (`assets/js/instagram-feed.js`, стили — `assets/css/instagram-feed.css`).

Откуда берутся посты:

1. `api/instagram.php` — живые данные через официальный Instagram API (Instagram Login). Ответ кэшируется на 30 минут, обложки сохраняются в `public_html/media/instagram/`, токен продлевается автоматически раз в неделю (живёт 60 дней).
2. Пока токена нет или API недоступен — `assets/data/instagram.json`: снимок 12 публикаций на 28.10.2026 (обложки в `assets/img/instagram/`).

Нужны PHP 8.0+ с расширением curl.

### Подключение токена (один раз)

1. Аккаунт Instagram должен быть профессиональным (Бизнес или Автор): Настройки → Тип аккаунта и инструменты.
2. [developers.facebook.com](https://developers.facebook.com/apps/) → Create App → добавить продукт **Instagram** → **API setup with Instagram login**.
3. В блоке «Generate access tokens» добавить аккаунт `costcontrol.services` (вход в Instagram) и нажать **Generate token**. Нужно разрешение `instagram_business_basic`.
4. В cPanel → File Manager создать папку `/home/ccscom/ccs-private/` (рядом с `public_html`, не внутри) и в ней файл `instagram-token.txt`, вставить туда токен.
5. Открыть https://ccs.com.az/api/instagram.php — в ответе должно быть `"source":"live"`.

Токен никогда не попадает в браузер и в репозиторий. Если его отозвать или он истечёт, сайт продолжит показывать последние сохранённые публикации; новый токен — просто заменить содержимое `instagram-token.txt` и удалить `instagram-state.json` рядом.

## Открытые вопросы (уточнить у заказчика)

- Email: в презентации стоит шуточная заглушка, на сайте пока `info@ccs.com.az`.
- Тексты первого экрана, подзаголовки, этапы работы (V2), FAQ (V2) и «болевые точки» (V3) написаны по мотивам презентации — нужна вычитка.
- Цифры в P&L-карточке V2 и значения калькулятора V3 — иллюстративные.
- Переключатель AZ / RU / EN пока только визуальный.

## Этап 2 — PHP

После утверждения варианта: партиалы (header/footer/секции), обработчик формы заявки, языковые версии.

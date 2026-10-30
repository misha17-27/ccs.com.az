<?php
/**
 * What the admin can edit: site texts (per content language), pages and settings,
 * plus the initial content of the site (design V1) in AZ, RU and EN.
 *
 * Texts may use light markup, see rich(): *word* — accent colour, **word** — bold, line break — new line.
 */

declare(strict_types=1);
defined('CCS') || exit;

/** Text groups shown as tabs on the "Texts" page. SEO is edited per page (see PAGES). */
const TEXT_GROUPS = [
    'hero' => ['az' => 'Əsas ekran', 'ru' => 'Главный экран'],
    'facts' => ['az' => 'Rəqəmlər', 'ru' => 'Цифры'],
    'about' => ['az' => 'Haqqımızda', 'ru' => 'О нас'],
    'services' => ['az' => 'Xidmətlər bölməsi', 'ru' => 'Блок услуг'],
    'calc' => ['az' => 'Kalkulyator', 'ru' => 'Калькулятор'],
    'clients' => ['az' => 'Müştərilər', 'ru' => 'Клиенты'],
    'experience' => ['az' => 'Təcrübə', 'ru' => 'Опыт'],
    'faq' => ['az' => 'Suallar bölməsi', 'ru' => 'Блок вопросов'],
    'contact' => ['az' => 'Əlaqə bölməsi', 'ru' => 'Блок контактов'],
    'form' => ['az' => 'Müraciət forması', 'ru' => 'Форма заявки'],
    'blog' => ['az' => 'Bloq', 'ru' => 'Блог'],
    'tiktok' => ['az' => 'TikTok', 'ru' => 'TikTok'],
    'instagram' => ['az' => 'Instagram', 'ru' => 'Instagram'],
    'footer' => ['az' => 'Footer və 404', 'ru' => 'Подвал и 404'],
];

/** key => [group, label az, label ru, 'line'|'area', default ['az' => …, 'ru' => …, 'en' => …]] */
const TEXT_FIELDS = [
    'hero.eyebrow' => ['hero', 'Kiçik başlıq', 'Надзаголовок', 'line', [
        'az' => 'Restoran auditi və xərclərə nəzarət · Bakı',
        'ru' => 'Аудит ресторанов и контроль затрат · Баку',
        'en' => 'Restaurant audit and cost control · Baku',
    ]],
    'hero.title' => ['hero', 'Başlıq (səhifənin H1-i)', 'Заголовок (H1 страницы)', 'line', [
        'az' => 'Restoran auditi — *hər qəpiyiniz* nəzarət altında',
        'ru' => 'Аудит ресторана — *каждый манат* под контролем',
        'en' => 'Restaurant audit — *every manat* under control',
    ]],
    'hero.lead' => ['hero', 'Mətn', 'Текст', 'area', [
        'az' => 'Restoran biznesində uğurun sirri təkcə dadlı yemək deyil. Restoran auditi ilə hər bir xammalın, resursun və maliyyə axınının dəqiq uçotunu qurur, gizli israfları və boşluqları dərhal aşkarlayırıq.',
        'ru' => 'Успех ресторана — это не только вкусная еда. Проводим аудит ресторана, выстраиваем точный учёт каждого продукта, ресурса и денежного потока и сразу находим скрытые потери и слабые места.',
        'en' => 'A successful restaurant takes more than great food. With a restaurant audit, we set up precise accounting for every ingredient, resource and cash flow, and quickly uncover hidden waste and gaps.',
    ]],
    'hero.cta' => ['hero', 'Düymə mətni', 'Текст кнопки', 'line', [
        'az' => 'Restoran auditi sifariş et',
        'ru' => 'Заказать аудит ресторана',
        'en' => 'Order a restaurant audit',
    ]],
    'hero.card_title' => ['hero', 'Kartın başlığı', 'Заголовок карточки', 'line', [
        'az' => 'Restoranınız üçün',
        'ru' => 'Для вашего ресторана',
        'en' => 'For your restaurant',
    ]],
    'hero.card_items' => ['hero', 'Kartdakı siyahı (hər sətir — bir bənd)', 'Список в карточке (каждая строка — пункт)', 'area', [
        'az' => "Texnoloji kartlar\nMaya dəyəri\nAnbar sayımları\nAylıq P&L hesabatları",
        'ru' => "Технологические карты\nСебестоимость\nИнвентаризация склада\nЕжемесячные отчёты P&L",
        'en' => "Recipe cards\nFood costing\nStock counts\nMonthly P&L reports",
    ]],

    'facts.1_num' => ['facts', 'Rəqəm 1', 'Цифра 1', 'line', ['az' => '2021', 'ru' => '2021', 'en' => '2021']],
    'facts.1_label' => ['facts', 'Rəqəm 1 — izah', 'Цифра 1 — подпись', 'line', [
        'az' => 'ilin sonundan iaşə sektorunda',
        'ru' => 'год, когда мы начали работать в общепите',
        'en' => 'the year we started in food service',
    ]],
    'facts.2_num' => ['facts', 'Rəqəm 2', 'Цифра 2', 'line', ['az' => '18', 'ru' => '18', 'en' => '18']],
    'facts.2_label' => ['facts', 'Rəqəm 2 — izah', 'Цифра 2 — подпись', 'line', [
        'az' => 'tərəfdaş restoran və brend',
        'ru' => 'ресторанов и брендов-партнёров',
        'en' => 'partner restaurants and brands',
    ]],
    'facts.3_num' => ['facts', 'Rəqəm 3', 'Цифра 3', 'line', ['az' => '6', 'ru' => '6', 'en' => '6']],
    'facts.3_label' => ['facts', 'Rəqəm 3 — izah', 'Цифра 3 — подпись', 'line', [
        'az' => 'xidmət istiqaməti',
        'ru' => 'направлений услуг',
        'en' => 'service areas',
    ]],
    'facts.4_num' => ['facts', 'Rəqəm 4', 'Цифра 4', 'line', ['az' => '200+', 'ru' => '200+', 'en' => '200+']],
    'facts.4_label' => ['facts', 'Rəqəm 4 — izah', 'Цифра 4 — подпись', 'line', [
        'az' => 'ölkənin qatıldığı çempionatda qida nəzarəti',
        'ru' => 'стран на чемпионате, где мы контролировали питание',
        'en' => 'countries at the championship where we ran food control',
    ]],

    'about.eyebrow' => ['about', 'Kiçik başlıq', 'Надзаголовок', 'line', ['az' => 'Haqqımızda', 'ru' => 'О нас', 'en' => 'About us']],
    'about.title' => ['about', 'Başlıq', 'Заголовок', 'line', ['az' => 'Biz kimik?', 'ru' => 'Кто мы?', 'en' => 'Who we are']],
    'about.since' => ['about', 'Başlığın altında', 'Под заголовком', 'line', [
        'az' => '2021-ci ilin sonundan bəri',
        'ru' => 'С конца 2021 года',
        'en' => 'Since late 2021',
    ]],
    'about.p1' => ['about', 'Abzas 1', 'Абзац 1', 'area', [
        'az' => '“Cost Control” şirkəti post-pandemiya dövrünün çətin və dinamik reallıqları fonunda — 2021-ci ilin sonlarından etibarən iaşə və restoran sektorunda peşəkar idarəetmə boşluqlarını doldurmaq məqsədilə təsis edilmişdir. Bu gün Bakıda restoran auditi və xərclərə nəzarət üzrə ixtisaslaşırıq.',
        'ru' => 'Компания «Cost Control» основана в конце 2021 года, на фоне непростых и быстро меняющихся реалий постпандемийного времени, чтобы восполнить нехватку профессионального управления в ресторанной сфере и общепите. Сегодня мы специализируемся на аудите ресторанов и контроле затрат в Баку.',
        'en' => 'Cost Control was founded in late 2021, amid the challenging and fast-changing realities of the post-pandemic period, to fill the gap in professional management across the restaurant and food service sector. Today we specialise in restaurant audits and cost control in Baku.',
    ]],
    'about.p2' => ['about', 'Abzas 2', 'Абзац 2', 'area', [
        'az' => 'Restoran biznesində uğurun sirri təkcə dadlı yemək deyil, hər bir xammalın, resursun və maliyyə axınının dəqiq uçotundan asılıdır. Restoran auditi və Cost Control mexanizmlərimiz sayəsində biznesdəki **gizli israflar, artan maya dəyəri** və **ehtiyatların idarə olunmasındakı boşluqlar** dərhal aşkarlanır.',
        'ru' => 'Успех ресторана зависит не только от вкусной еды, но и от точного учёта каждого продукта, ресурса и денежного потока. Аудит ресторана и наши механизмы cost control сразу выявляют **скрытые потери, рост себестоимости** и **пробелы в управлении запасами**.',
        'en' => 'Restaurant success depends not only on great food but on precise accounting of every ingredient, resource and cash flow. Our restaurant audit and cost control mechanisms quickly reveal **hidden waste, rising food costs** and **gaps in inventory management**.',
    ]],
    'about.p3' => ['about', 'Abzas 3', 'Абзац 3', 'area', [
        'az' => 'Böyük şəbəkələrin idarə olunmasında qazanılmış qabaqcıl təcrübələr və standartlar kiçik və orta ölçülü restoranlara uyğunlaşdırılır. Bu yanaşma həm yerli bazarda yeni obyektlərin sıfırdan qurulmasına münbit şərait yaradır, həm də mövcud restoranların gəlirliliyini maksimum səviyyəyə qaldırır.',
        'ru' => 'Передовые практики и стандарты, отработанные в крупных сетях, мы адаптируем для небольших и средних ресторанов. Такой подход помогает с нуля запускать новые заведения на местном рынке и выводить доходность действующих ресторанов на максимум.',
        'en' => 'We adapt best practices and standards proven in large chains to small and mid-sized restaurants. This approach helps launch new venues from scratch in the local market and pushes the profitability of existing restaurants to its maximum.',
    ]],

    'services.eyebrow' => ['services', 'Kiçik başlıq', 'Надзаголовок', 'line', [
        'az' => 'Restoran auditi xidmətləri',
        'ru' => 'Услуги аудита ресторана',
        'en' => 'Restaurant audit services',
    ]],
    'services.title' => ['services', 'Başlıq', 'Заголовок', 'line', [
        'az' => 'Altı istiqamət, bir məqsəd — *gəlirli restoran*',
        'ru' => 'Шесть направлений, одна цель — *прибыльный ресторан*',
        'en' => 'Six areas, one goal — *a profitable restaurant*',
    ]],
    'services.lead' => ['services', 'Mətn', 'Текст', 'area', [
        'az' => 'Restoran auditi maliyyə uçotundan anbar sayımlarına qədər bütün xərc zəncirini əhatə edir — biz onu şəffaf və idarə olunan edirik.',
        'ru' => 'Аудит ресторана охватывает всю цепочку затрат — от финансового учёта до инвентаризации склада. Мы делаем её прозрачной и управляемой.',
        'en' => 'A restaurant audit covers the whole cost chain, from financial accounting to stock counts. We make it transparent and manageable.',
    ]],

    'calc.eyebrow' => ['calc', 'Kiçik başlıq', 'Надзаголовок', 'line', ['az' => 'Alət', 'ru' => 'Инструмент', 'en' => 'Tool']],
    'calc.title' => ['calc', 'Başlıq', 'Заголовок', 'line', [
        'az' => 'Maya dəyəri *kalkulyatoru*',
        'ru' => 'Калькулятор *себестоимости*',
        'en' => '*Food cost* calculator',
    ]],
    'calc.lead' => ['calc', 'Mətn', 'Текст', 'area', [
        'az' => 'Bir yeməyin food cost faizini və marjasını saniyələr içində yoxlayın. Hədəf faizi özünüz təyin edin.',
        'ru' => 'Проверьте фудкост и маржу блюда за несколько секунд. Целевой процент задаёте сами.',
        'en' => 'Check the food cost percentage and margin of a dish in seconds. You set the target.',
    ]],
    'calc.note' => ['calc', 'Qeyd', 'Примечание', 'area', [
        'az' => 'Bu, ilkin təsəvvür üçün sadə hesablamadır. Hər yemək üçün dəqiq maya dəyəri texnoloji kart əsasında hesablanır — bu, «Maya dəyəri və texnoloji kartlar» xidmətimizin bir hissəsidir.',
        'ru' => 'Это простой расчёт для первого представления. Точная себестоимость каждого блюда считается по технологической карте — это часть нашей услуги «Себестоимость и технологические карты».',
        'en' => 'This is a simple estimate to give you a first idea. The exact cost of each dish is calculated from its recipe card — part of our “Food costing and recipe cards” service.',
    ]],
    'calc.cta' => ['calc', 'Düymə mətni', 'Текст кнопки', 'line', [
        'az' => 'Menyunu birlikdə hesablayaq',
        'ru' => 'Посчитаем меню вместе',
        'en' => 'Let’s cost your menu together',
    ]],

    'clients.eyebrow' => ['clients', 'Kiçik başlıq', 'Надзаголовок', 'line', ['az' => 'Müştərilərimiz', 'ru' => 'Наши клиенты', 'en' => 'Our clients']],
    'clients.title' => ['clients', 'Başlıq', 'Заголовок', 'line', [
        'az' => 'Çalışdığımız şirkətlər',
        'ru' => 'Компании, с которыми мы работаем',
        'en' => 'Companies we work with',
    ]],
    'clients.lead' => ['clients', 'Mətn', 'Текст', 'area', [
        'az' => 'Restoran, kafe, lounge və otellər — restoran auditi və xərclərə nəzarət üzrə müxtəlif formatlı 18 brendlə əməkdaşlıq etmişik.',
        'ru' => 'Рестораны, кафе, лаунжи и отели — по аудиту и контролю затрат мы работали с 18 брендами разных форматов.',
        'en' => 'Restaurants, cafés, lounges and hotels — we have worked with 18 brands of different formats on restaurant audits and cost control.',
    ]],

    'experience.eyebrow' => ['experience', 'Kiçik başlıq', 'Надзаголовок', 'line', [
        'az' => 'Beynəlxalq və xüsusi təcrübə',
        'ru' => 'Международный и особый опыт',
        'en' => 'International and special experience',
    ]],
    'experience.title' => ['experience', 'Başlıq', 'Заголовок', 'line', [
        'az' => 'Dünya çempionatından *milli mətbəx* layihələrinə',
        'ru' => 'От чемпионата мира до проектов *национальной кухни*',
        'en' => 'From a world championship to *national cuisine* projects',
    ]],

    'faq.eyebrow' => ['faq', 'Kiçik başlıq', 'Надзаголовок', 'line', ['az' => 'Suallar', 'ru' => 'Вопросы', 'en' => 'FAQ']],
    'faq.title' => ['faq', 'Başlıq', 'Заголовок', 'line', [
        'az' => 'Restoran auditi: tez-tez verilən suallar',
        'ru' => 'Аудит ресторана: частые вопросы',
        'en' => 'Restaurant audit: frequently asked questions',
    ]],
    'faq.lead' => ['faq', 'Mətn', 'Текст', 'area', [
        'az' => 'Cavabını tapmadığınız sual varsa, bizə birbaşa yazın — məmnuniyyətlə izah edərik.',
        'ru' => 'Не нашли ответ на свой вопрос? Напишите нам напрямую — с радостью объясним.',
        'en' => 'Didn’t find your answer? Write to us directly — we’ll be happy to explain.',
    ]],

    'contact.eyebrow' => ['contact', 'Kiçik başlıq', 'Надзаголовок', 'line', ['az' => 'Əlaqə', 'ru' => 'Контакты', 'en' => 'Contact']],
    'contact.title' => ['contact', 'Başlıq', 'Заголовок', 'line', [
        'az' => 'Restoranınızın rəqəmlərini *birlikdə yoxlayaq*',
        'ru' => 'Давайте *вместе проверим* цифры вашего ресторана',
        'en' => 'Let’s *check your restaurant’s numbers* together',
    ]],
    'contact.lead' => ['contact', 'Mətn', 'Текст', 'area', [
        'az' => 'Restoran auditi üçün müraciət qoyun — mütəxəssisimiz sizinlə əlaqə saxlayacaq və restoranınız üçün uyğun həlli müzakirə edəcək.',
        'ru' => 'Оставьте заявку на аудит ресторана — наш специалист свяжется с вами и обсудит подходящее решение для вашего заведения.',
        'en' => 'Leave a request for a restaurant audit and our specialist will contact you to discuss the right solution for your venue.',
    ]],
    'contact.address' => ['contact', 'Ünvan', 'Адрес', 'line', [
        'az' => 'Bakı şəhəri, Nizami rayonu, Heydər Əliyev prospekti 5',
        'ru' => 'г. Баку, Низаминский район, проспект Гейдара Алиева, 5',
        'en' => '5 Heydar Aliyev Avenue, Nizami district, Baku',
    ]],
    'contact.form_title' => ['contact', 'Formanın başlığı', 'Заголовок формы', 'line', [
        'az' => 'Restoran auditi üçün müraciət',
        'ru' => 'Заявка на аудит ресторана',
        'en' => 'Request a restaurant audit',
    ]],
    'contact.success_title' => ['contact', 'Göndərildikdən sonra — başlıq', 'После отправки — заголовок', 'line', [
        'az' => 'Təşəkkür edirik!',
        'ru' => 'Спасибо!',
        'en' => 'Thank you!',
    ]],
    'contact.success' => ['contact', 'Göndərildikdən sonra — mətn', 'После отправки — текст', 'area', [
        'az' => 'Müraciətiniz qəbul olundu. Tezliklə sizinlə əlaqə saxlayacağıq.',
        'ru' => 'Ваша заявка принята. Мы скоро с вами свяжемся.',
        'en' => 'Your request has been received. We will contact you soon.',
    ]],

    'instagram.text' => ['instagram', 'Mətn', 'Текст', 'area', [
        'az' => 'Restoran biznesi üçün yeni videolar və praktiki məsləhətlər — mütəmadi olaraq Instagram səhifəmizdə.',
        'ru' => 'Новые видео и практические советы для ресторанного бизнеса — регулярно в нашем Instagram.',
        'en' => 'New videos and practical tips for the restaurant business — regularly on our Instagram.',
    ]],

    'tiktok.eyebrow' => ['tiktok', 'Kiçik başlıq', 'Надзаголовок', 'line', ['az' => 'TikTok', 'ru' => 'TikTok', 'en' => 'TikTok']],
    'tiktok.title' => ['tiktok', 'Başlıq', 'Заголовок', 'line', [
        'az' => 'Real layihələr — *videoda*',
        'ru' => 'Реальные проекты — *на видео*',
        'en' => 'Real projects — *on video*',
    ]],
    'tiktok.lead' => ['tiktok', 'Mətn', 'Текст', 'area', [
        'az' => 'Restoranlarda gördüyümüz işlər, xərclərə nəzarət üzrə məsləhətlər və yeni əməkdaşlıqlar — qısa videolarla TikTok səhifəmizdə.',
        'ru' => 'Наша работа в ресторанах, советы по контролю затрат и новые проекты — в коротких видео в нашем TikTok.',
        'en' => 'Our work in restaurants, cost control tips and new partnerships — in short videos on our TikTok.',
    ]],

    'footer.text' => ['footer', 'Qısa təsvir', 'Краткое описание', 'area', [
        'az' => 'Bakıda restoran auditi və xərclərə nəzarət: maya dəyəri, inventar, P&L. 2021-ci ildən bəri.',
        'ru' => 'Аудит ресторанов и контроль затрат в Баку: себестоимость, инвентаризация, P&L. С 2021 года.',
        'en' => 'Restaurant audit and cost control in Baku: food costing, inventory, P&L. Since 2021.',
    ]],
];

/**
 * Interface strings of the site that Admin → Texts can change: key in app/site_lang => [group, label az, label ru, 'line'|'area'].
 * Their defaults stay in app/site_lang/{lang}.php; a changed value is saved as the text 'ui.<key>' (see ui()).
 */
const UI_FIELDS = [
    'cta_short' => ['hero', 'Yuxarı paneldəki qısa düymə (bütün səhifələrdə)', 'Короткая кнопка в шапке (на всех страницах)', 'line'],
    'svc_apply' => ['services', '«Bu xidmət üzrə müraciət et» linki', 'Ссылка «Оставить заявку на эту услугу»', 'line'],

    'calc_cost' => ['calc', 'Sahə: maya dəyəri', 'Поле: себестоимость', 'line'],
    'calc_cost_default' => ['calc', 'Maya dəyəri — ilkin rəqəm', 'Себестоимость — начальное значение', 'line'],
    'calc_price' => ['calc', 'Sahə: satış qiyməti', 'Поле: цена продажи', 'line'],
    'calc_price_default' => ['calc', 'Satış qiyməti — ilkin rəqəm', 'Цена продажи — начальное значение', 'line'],
    'calc_target' => ['calc', 'Sahə: hədəf food cost', 'Поле: целевой фудкост', 'line'],
    'calc_fc' => ['calc', 'Nəticə: food cost', 'Результат: фудкост', 'line'],
    'calc_target_mark' => ['calc', 'Şkalada «hədəf» işarəsi', 'Отметка «цель» на шкале', 'line'],
    'calc_margin' => ['calc', 'Nəticə: marja / porsiya', 'Результат: маржа / порция', 'line'],
    'calc_margin_pct' => ['calc', 'Nəticə: marja faizi', 'Результат: процент маржи', 'line'],
    'calc_rec' => ['calc', 'Nəticə: hədəf üçün qiymət', 'Результат: цена для цели', 'line'],
    'calc_ok' => ['calc', 'Status: hədəf daxilində', 'Статус: в пределах цели', 'line'],
    'calc_warn' => ['calc', 'Status: hədəfdən yuxarı', 'Статус: выше цели', 'line'],
    'calc_idle' => ['calc', 'Status: rəqəmlər daxil edilməyib', 'Статус: цифры не введены', 'line'],
    'calc_hint_invalid' => ['calc', 'İzah: rəqəmlər səhvdir', 'Пояснение: неверные цифры', 'area'],
    'calc_hint_ok' => ['calc', 'İzah: hədəf daxilində ({max} — maksimum maya dəyəri)', 'Пояснение: в пределах цели ({max} — максимальная себестоимость)', 'area'],
    'calc_hint_warn' => ['calc', 'İzah: hədəfdən yuxarı ({rec} — tövsiyə olunan qiymət, {max} — maksimum maya dəyəri)', 'Пояснение: выше цели ({rec} — рекомендуемая цена, {max} — максимальная себестоимость)', 'area'],

    'faq_ask' => ['faq', 'WhatsApp-da sual linki', 'Ссылка «Задать вопрос в WhatsApp»', 'line'],

    'contact_address' => ['contact', 'Sahənin adı: ünvan', 'Подпись: адрес', 'line'],
    'contact_phone' => ['contact', 'Sahənin adı: telefon', 'Подпись: телефон', 'line'],
    'contact_whatsapp' => ['contact', 'Sahənin adı: WhatsApp', 'Подпись: WhatsApp', 'line'],
    'contact_email' => ['contact', 'Sahənin adı: e-poçt', 'Подпись: e-mail', 'line'],
    'contact_instagram' => ['contact', 'Sahənin adı: Instagram', 'Подпись: Instagram', 'line'],
    'wa_label' => ['contact', 'WhatsApp düyməsi (sağ aşağı küncdə)', 'Кнопка WhatsApp (справа внизу)', 'line'],
    'wa_greeting' => ['contact', 'WhatsApp-da hazır salam mesajı', 'Готовое приветствие в WhatsApp', 'area'],

    'form_name' => ['form', 'Ad sahəsi', 'Поле имени', 'line'],
    'form_name_ph' => ['form', 'Ad sahəsində nümunə', 'Подсказка в поле имени', 'line'],
    'form_name_err' => ['form', 'Ad yazılmayanda xəta', 'Ошибка, если имя не указано', 'line'],
    'form_phone' => ['form', 'Telefon sahəsi', 'Поле телефона', 'line'],
    'form_phone_err' => ['form', 'Telefon səhv olanda xəta', 'Ошибка в номере телефона', 'line'],
    'form_company' => ['form', 'Restoran / şirkət sahəsi', 'Поле «Ресторан / компания»', 'line'],
    'form_company_ph' => ['form', 'Restoran sahəsində nümunə', 'Подсказка в поле ресторана', 'line'],
    'form_service' => ['form', 'Xidmət sahəsi', 'Поле услуги', 'line'],
    'form_service_ph' => ['form', 'Xidmət seçilməyəndə', 'Когда услуга не выбрана', 'line'],
    'form_service_any' => ['form', '«Kompleks yanaşma» variantı', 'Вариант «Комплексный подход»', 'line'],
    'form_message' => ['form', 'Mesaj sahəsi', 'Поле сообщения', 'line'],
    'form_message_ph' => ['form', 'Mesaj sahəsində nümunə', 'Подсказка в поле сообщения', 'line'],
    'form_submit' => ['form', 'Göndər düyməsi', 'Кнопка отправки', 'line'],
    'form_note' => ['form', 'Düymənin altında qeyd', 'Примечание под кнопкой', 'line'],
    'form_wa_hint' => ['form', 'Göndərildikdən sonra: WhatsApp xatırlatması', 'После отправки: напоминание про WhatsApp', 'area'],
    'form_wa_open' => ['form', 'Göndərildikdən sonra: WhatsApp düyməsi', 'После отправки: кнопка WhatsApp', 'line'],
    'form_again' => ['form', 'Göndərildikdən sonra: «Yeni müraciət» düyməsi', 'После отправки: кнопка «Новая заявка»', 'line'],
    'wa_msg_intro' => ['form', 'WhatsApp mesajı: ilk sətir', 'Сообщение в WhatsApp: первая строка', 'line'],
    'wa_msg_name' => ['form', 'WhatsApp mesajı: «Ad»', 'Сообщение в WhatsApp: «Имя»', 'line'],
    'wa_msg_phone' => ['form', 'WhatsApp mesajı: «Telefon»', 'Сообщение в WhatsApp: «Телефон»', 'line'],
    'wa_msg_company' => ['form', 'WhatsApp mesajı: «Restoran»', 'Сообщение в WhatsApp: «Ресторан»', 'line'],
    'wa_msg_service' => ['form', 'WhatsApp mesajı: «Xidmət»', 'Сообщение в WhatsApp: «Услуга»', 'line'],
    'wa_msg_message' => ['form', 'WhatsApp mesajı: «Mesaj»', 'Сообщение в WhatsApp: «Сообщение»', 'line'],

    'blog_read' => ['blog', 'Məqalə kartında «Ətraflı oxu» linki', 'Ссылка «Читать статью» в карточке', 'line'],
    'blog_back' => ['blog', 'Məqalənin altında «Bütün məqalələr» linki', 'Ссылка «Все статьи» под статьёй', 'line'],
    'blog_empty' => ['blog', 'Hələ məqalə olmayanda mətn', 'Текст, пока статей нет', 'area'],

    'tt_follow' => ['tiktok', 'Düymə mətni', 'Текст кнопки', 'line'],
    'ig_follow' => ['instagram', 'Düymə mətni', 'Текст кнопки', 'line'],

    'footer_nav' => ['footer', 'Footer: menyunun başlığı', 'Подвал: заголовок меню', 'line'],
    'footer_services' => ['footer', 'Footer: xidmətlərin başlığı', 'Подвал: заголовок услуг', 'line'],
    'footer_contact' => ['footer', 'Footer: əlaqənin başlığı', 'Подвал: заголовок контактов', 'line'],
    'footer_rights' => ['footer', 'Footer: «Bütün hüquqlar qorunur»', 'Подвал: «Все права защищены»', 'line'],
    'footer_top' => ['footer', 'Footer: «Yuxarı» linki', 'Подвал: ссылка «Наверх»', 'line'],
    'not_found_title' => ['footer', '404 səhifəsi: başlıq', 'Страница 404: заголовок', 'line'],
    'not_found_text' => ['footer', '404 səhifəsi: mətn', 'Страница 404: текст', 'area'],
    'not_found_home' => ['footer', '404 səhifəsi: düymə', 'Страница 404: кнопка', 'line'],
];

/**
 * Site pages: fixed keys with an editable address per language ('slug' is AZ, 'slug_ru' and 'slug_en' follow /ru/ and /en/),
 * title (H1) and SEO. 'sections' are the blocks the page
 * shows (app/views/sections); the page text lives in the 'groups' of TEXT_FIELDS and in the
 * 'items' tables, and their translations make up the "Text" status in the admin.
 */
const PAGES = [
    'home' => ['az' => 'Ana səhifə', 'ru' => 'Главная', 'slug' => '', 'slug_ru' => '', 'slug_en' => '', 'groups' => ['hero', 'facts', 'calc', 'tiktok', 'instagram'], 'items' => [],
        'sections' => ['hero', 'about', 'services', 'calc', 'clients', 'experience', 'tiktok', 'contact', 'instagram']],
    'about' => ['az' => 'Haqqımızda', 'ru' => 'О нас', 'slug' => 'haqqimizda', 'slug_ru' => 'o-nas', 'slug_en' => 'about-us', 'groups' => ['about', 'experience'], 'items' => ['projects'],
        'sections' => ['about', 'experience', 'clients', 'contact']],
    'services' => ['az' => 'Xidmətlər (siyahı)', 'ru' => 'Услуги (список)', 'slug' => 'xidmetler', 'slug_ru' => 'uslugi', 'slug_en' => 'services', 'groups' => ['services', 'calc'], 'items' => ['services'],
        'sections' => ['services', 'calc', 'contact']],
    'clients' => ['az' => 'Müştərilər', 'ru' => 'Клиенты', 'slug' => 'musteriler', 'slug_ru' => 'klienty', 'slug_en' => 'clients', 'groups' => ['clients'], 'items' => [],
        'sections' => ['clients', 'experience', 'contact']],
    'faq' => ['az' => 'Suallar', 'ru' => 'Вопросы', 'slug' => 'suallar', 'slug_ru' => 'voprosy', 'slug_en' => 'faq', 'groups' => ['faq'], 'items' => ['faqs'],
        'sections' => ['faq', 'contact']],
    // Blog: Admin → Blog shows or hides it (menu, footer, sitemap, search engines); posts live at /bloq/<post>
    'blog' => ['az' => 'Bloq', 'ru' => 'Блог', 'slug' => 'bloq', 'slug_ru' => 'blog', 'slug_en' => 'blog', 'groups' => ['blog'], 'items' => ['posts'],
        'sections' => ['blog', 'contact']],
    'contact' => ['az' => 'Əlaqə', 'ru' => 'Контакты', 'slug' => 'elaqe', 'slug_ru' => 'kontakty', 'slug_en' => 'contact', 'groups' => ['contact'], 'items' => [],
        'sections' => ['contact', 'tiktok', 'instagram']],
];

/** Addresses the site itself uses, so pages cannot take them. */
const RESERVED_SLUGS = ['admin', 'api', 'app', 'assets', 'media', 'uploads', 'index', 'sitemap', 'v1', 'v2', 'v3', 'az', 'ru', 'en'];

/** Settings page fields: key => [section, label az, label ru, input type, default] */
const SETTING_FIELDS = [
    'contact_phone' => ['contacts', 'Telefon', 'Телефон', 'text', '+994 55 855 61 52'],
    'contact_whatsapp' => ['contacts', 'WhatsApp nömrəsi (yalnız rəqəmlər)', 'Номер WhatsApp (только цифры)', 'text', '994558556152'],
    'contact_email' => ['contacts', 'E-poçt', 'E-mail', 'email', 'info@ccs.com.az'],
    'contact_instagram' => ['contacts', 'Instagram linki', 'Ссылка на Instagram', 'url', 'https://www.instagram.com/costcontrol.services/'],
    'contact_tiktok' => ['contacts', 'TikTok linki', 'Ссылка на TikTok', 'url', 'https://www.tiktok.com/@costcontrol.services'],
    'contact_map' => ['contacts', 'Google Maps linki', 'Ссылка на Google Maps', 'url', ''],
    'notify_email' => ['notify', 'Yeni sorğular bu ünvana göndərilsin', 'Отправлять новые заявки на адрес', 'email', ''],
    'turnstile_site_key' => ['security', 'Turnstile Site Key', 'Turnstile Site Key', 'text', ''],
    'turnstile_secret_key' => ['security', 'Turnstile Secret Key', 'Turnstile Secret Key', 'password', ''],
];

const SETTING_SECTIONS = [
    'contacts' => ['az' => 'Əlaqə məlumatları', 'ru' => 'Контактные данные'],
    'notify' => ['az' => 'Bildirişlər', 'ru' => 'Уведомления'],
    'security' => ['az' => 'Giriş qorunması (Cloudflare Turnstile)', 'ru' => 'Защита входа (Cloudflare Turnstile)'],
];

/** Icons available for services (see icon() in the admin and the sprite in app/views/layout.php). */
const SERVICE_ICONS = ['finance', 'analysis', 'optimize', 'recipe', 'inventory', 'shield'];

/** [icon, image, title, lead, [[point title, point text], …]]; every text is ['az' => …, 'ru' => …, 'en' => …] */
const SEED_SERVICES = [
    ['finance', '/assets/img/render/wallet.jpg',
        ['az' => 'Maliyyə və P&L', 'ru' => 'Финансы и P&L', 'en' => 'Finance and P&L'],
        [
            'az' => 'Restoranın maliyyəsini şəffaf, rəqəmləri isə qərar qəbul etmək üçün aydın edirik.',
            'ru' => 'Делаем финансы ресторана прозрачными, а цифры — понятными для принятия решений.',
            'en' => 'We make your restaurant’s finances transparent and the numbers clear enough to base decisions on.',
        ], [
            [
                ['az' => 'Şəffaf maliyyə sistemi', 'ru' => 'Прозрачная финансовая система', 'en' => 'A transparent financial system'],
                [
                    'az' => 'Restoranın bütün gəlir və xərclərinin qeydiyyatı üçün etibarlı uçot mexanizminin qurulması.',
                    'ru' => 'Настройка надёжного учёта всех доходов и расходов ресторана.',
                    'en' => 'Setting up reliable accounting for all of the restaurant’s income and expenses.',
                ],
            ], [
                ['az' => 'Aylıq P&L (mənfəət və zərər) hesabatları', 'ru' => 'Ежемесячные отчёты P&L (прибыли и убытки)', 'en' => 'Monthly P&L (profit and loss) reports'],
                [
                    'az' => 'Rəhbərliyin qərar qəbul etməsi üçün hər ay üzrə maliyyə nəticələrinin dəqiq və şifrəsiz təhlili.',
                    'ru' => 'Точный и понятный разбор финансовых результатов за каждый месяц — для решений руководства.',
                    'en' => 'A precise, plain-language analysis of each month’s financial results to support management decisions.',
                ],
            ], [
                ['az' => 'Pul axınının idarə edilməsi', 'ru' => 'Управление денежным потоком', 'en' => 'Cash flow management'],
                [
                    'az' => 'Nağd və nağdsız vəsaitlərin hərəkətinə daimi nəzarət.',
                    'ru' => 'Постоянный контроль движения наличных и безналичных средств.',
                    'en' => 'Ongoing control over the movement of cash and cashless funds.',
                ],
            ],
        ]],
    ['analysis', '/assets/img/render/chart.jpg',
        ['az' => 'Xərc analizi', 'ru' => 'Анализ затрат', 'en' => 'Cost analysis'],
        [
            'az' => 'Hər qəpiyin hara getdiyini görürük — gizli itkilərdən xərclərin effektivliyinə qədər.',
            'ru' => 'Видим, куда уходит каждый манат, — от скрытых потерь до эффективности расходов.',
            'en' => 'We see where every manat goes — from hidden losses to the efficiency of spending.',
        ], [
            [
                ['az' => 'İtkilərin aşkar edilməsi', 'ru' => 'Выявление потерь', 'en' => 'Finding losses'],
                [
                    'az' => 'Proseslərdə və ya mətbəxdə baş verən gizli israf və artıq xərclərin müəyyənləşdirilməsi.',
                    'ru' => 'Поиск скрытых потерь и лишних расходов в процессах и на кухне.',
                    'en' => 'Identifying hidden waste and excess costs in processes or in the kitchen.',
                ],
            ], [
                ['az' => 'Xərclərin izlənməsi', 'ru' => 'Отслеживание расходов', 'en' => 'Expense tracking'],
                [
                    'az' => 'Biznesə daxil olan hər bir qəpiyin hara xərcləndiyinin şəffaf şəkildə auditi və hesabatı.',
                    'ru' => 'Прозрачный аудит и отчётность о том, куда тратится каждый манат бизнеса.',
                    'en' => 'A transparent audit and report of where every manat coming into the business is spent.',
                ],
            ], [
                ['az' => 'Effektivlik təhlili', 'ru' => 'Анализ эффективности', 'en' => 'Efficiency analysis'],
                [
                    'az' => 'Xərclərin real nəticə verib-vermədiyinin qiymətləndirilməsi.',
                    'ru' => 'Оценка того, приносят ли расходы реальный результат.',
                    'en' => 'Assessing whether spending delivers real results.',
                ],
            ],
        ]],
    ['optimize', '/assets/img/render/gear.jpg',
        ['az' => 'Xərclərin optimallaşdırılması', 'ru' => 'Оптимизация затрат', 'en' => 'Cost optimization'],
        [
            'az' => 'Keyfiyyəti qoruyaraq xərcləri təchizat, ştat və kommunal səviyyəsində tənzimləyirik.',
            'ru' => 'Сохраняя качество, настраиваем затраты на закупки, персонал и коммунальные услуги.',
            'en' => 'We fine-tune supply, staffing and utility costs without compromising on quality.',
        ], [
            [
                ['az' => 'Bazar xərclərinin tənzimlənməsi', 'ru' => 'Работа с закупочными ценами', 'en' => 'Aligning purchase prices'],
                [
                    'az' => 'Təchizatçı qiymətlərinin bazar dəyərinə uyğunlaşdırılması və sərfəli şərtlərin əldə olunması.',
                    'ru' => 'Приведение цен поставщиков к рыночным и получение выгодных условий.',
                    'en' => 'Bringing supplier prices in line with the market and securing better terms.',
                ],
            ], [
                ['az' => 'Əməkhaqqı fondunun optimallaşdırılması', 'ru' => 'Оптимизация фонда оплаты труда', 'en' => 'Payroll optimization'],
                [
                    'az' => 'Ştat cədvəlinin iş yükünə uyğun şəkildə düzgün qurulması.',
                    'ru' => 'Правильное штатное расписание под реальную нагрузку.',
                    'en' => 'Building a staffing schedule that matches the actual workload.',
                ],
            ], [
                ['az' => 'Kommunal və digər xərclər', 'ru' => 'Коммунальные и прочие расходы', 'en' => 'Utilities and other costs'],
                [
                    'az' => 'İdarəetmə xərclərinin minimuma endirilməsi üçün qənaət tədbirlərinin görülməsi.',
                    'ru' => 'Меры экономии, чтобы свести операционные расходы к минимуму.',
                    'en' => 'Savings measures that keep overheads to a minimum.',
                ],
            ],
        ]],
    ['recipe', '/assets/img/render/document.jpg',
        ['az' => 'Maya dəyəri və texnoloji kartlar', 'ru' => 'Себестоимость и технологические карты', 'en' => 'Food costing and recipe cards'],
        [
            'az' => 'Hər yeməyin real maya dəyərini bilir, qiymət siyasətini buna uyğun qururuq.',
            'ru' => 'Знаем реальную себестоимость каждого блюда и строим ценовую политику на её основе.',
            'en' => 'We know the real cost of every dish and build your pricing around it.',
        ], [
            [
                ['az' => 'Texnoloji kartların hazırlanması', 'ru' => 'Разработка технологических карт', 'en' => 'Creating recipe cards'],
                [
                    'az' => 'Hər bir yemək və içki üçün tərkib hissələrinin dəqiq reseptura ilə sistemə işlənməsi.',
                    'ru' => 'Точная рецептура каждого блюда и напитка, внесённая в систему.',
                    'en' => 'Entering a precise recipe for every dish and drink into the system.',
                ],
            ], [
                ['az' => 'Maya dəyərinin hesablanması', 'ru' => 'Расчёт себестоимости', 'en' => 'Calculating food cost'],
                [
                    'az' => 'Xammal qiymətlərindəki dəyişikliklərin məhsulun maya dəyərinə təsirinin dərhal izlənilməsi.',
                    'ru' => 'Мгновенное отслеживание того, как изменения цен на продукты влияют на себестоимость блюд.',
                    'en' => 'Tracking at once how changes in ingredient prices affect the cost of each dish.',
                ],
            ], [
                ['az' => 'Satış qiymətlərinin tənzimlənməsi', 'ru' => 'Настройка цен продажи', 'en' => 'Setting menu prices'],
                [
                    'az' => 'Optimal qazanc marjasını təmin etmək üçün qiymət siyasətinin qurulması.',
                    'ru' => 'Ценовая политика, обеспечивающая оптимальную маржу.',
                    'en' => 'A pricing policy that secures the optimal profit margin.',
                ],
            ],
        ]],
    ['inventory', '/assets/img/render/box.jpg',
        ['az' => 'Satınalma və inventar', 'ru' => 'Закупки и инвентаризация', 'en' => 'Purchasing and inventory'],
        [
            'az' => 'Sərfəli təchizat, dəqiq sayım və sağlam anbar dövriyyəsi.',
            'ru' => 'Выгодные закупки, точный учёт и здоровая оборачиваемость склада.',
            'en' => 'Cost-effective supply, accurate counts and healthy stock turnover.',
        ], [
            [
                ['az' => 'Sərfəli təchizat', 'ru' => 'Выгодные закупки', 'en' => 'Cost-effective supply'],
                [
                    'az' => 'Keyfiyyətdən ödün vermədən ən optimal satınalma şərtlərinin yaradılması.',
                    'ru' => 'Самые выгодные условия закупок без ущерба для качества.',
                    'en' => 'The best purchasing terms without compromising on quality.',
                ],
            ], [
                ['az' => 'Aylıq peşəkar sayımlar', 'ru' => 'Ежемесячная профессиональная инвентаризация', 'en' => 'Monthly professional stock counts'],
                [
                    'az' => 'Anbarda olan məhsulların dəqiq inventarizasiyası və qalıqların yoxlanılması.',
                    'ru' => 'Точная инвентаризация продуктов на складе и проверка остатков.',
                    'en' => 'Accurate inventory of the products in stock and checks of the balances.',
                ],
            ], [
                ['az' => 'Artıq ehtiyatların qarşısının alınması', 'ru' => 'Предотвращение излишков', 'en' => 'Preventing overstock'],
                [
                    'az' => 'Məhsulların xarab olma riskini azaltmaq üçün anbar dövriyyəsinin idarə edilməsi.',
                    'ru' => 'Управление оборачиваемостью склада, чтобы снизить риск порчи продуктов.',
                    'en' => 'Managing stock turnover to reduce the risk of spoilage.',
                ],
            ],
        ]],
    ['shield', '/assets/img/render/lock.jpg',
        ['az' => 'Daxili nəzarət və təhlükəsizlik', 'ru' => 'Внутренний контроль и безопасность', 'en' => 'Internal control and security'],
        [
            'az' => 'Səlahiyyətləri bölüşdürür, sui-istifadəni erkən mərhələdə aşkarlayırıq.',
            'ru' => 'Распределяем полномочия и выявляем злоупотребления на ранней стадии.',
            'en' => 'We divide responsibilities and detect misconduct early.',
        ], [
            [
                ['az' => 'Səlahiyyət bölgüsü', 'ru' => 'Распределение полномочий', 'en' => 'Division of responsibilities'],
                [
                    'az' => 'İşçi heyəti arasında vəzifə öhdəliklərinin dəqiq bölüşdürülməsi.',
                    'ru' => 'Чёткое распределение обязанностей между сотрудниками.',
                    'en' => 'A clear division of duties among staff.',
                ],
            ], [
                ['az' => 'Sui-istifadənin qarşısının alınması', 'ru' => 'Предотвращение злоупотреблений', 'en' => 'Preventing misconduct'],
                [
                    'az' => 'Müəssisədə yarana biləcək oğurluq və ya maliyyə pozuntularının erkən mərhələdə aşkarlanması və aradan qaldırılması.',
                    'ru' => 'Раннее выявление и устранение краж и финансовых нарушений в заведении.',
                    'en' => 'Detecting and stopping theft or financial violations at an early stage.',
                ],
            ], [
                ['az' => 'Nəzarət mexanizmləri', 'ru' => 'Механизмы контроля', 'en' => 'Control mechanisms'],
                [
                    'az' => 'Gündəlik əməliyyatların audit olunması.',
                    'ru' => 'Аудит ежедневных операций.',
                    'en' => 'Auditing daily operations.',
                ],
            ],
        ]],
];

/** [name, logo file in /assets/img/clients/] */
const SEED_PARTNERS = [
    ['Vasilchuki', 'vasilchuki'], ['Baribal', 'baribal'], ['Vətən', 'vatan'], ['The Real McCoy', 'the-real-mccoy'],
    ['Sess restobar', 'sess'], ['Green City Resort & Residence', 'green-city'], ['Şirvanşah muzey restoran', 'sirvansah'],
    ['Monkey Pub', 'monkey-pub'], ['Xarı Bülbül', 'xari-bulbul'], ['Gondola cafe & lounge', 'gondola'],
    ['Pivnaya Apteka', 'pivnaya-apteka'], ['Manzara', 'manzara'], ['Resant Hotels & Restaurants', 'resant'],
    ['Cəviz Tea & Lounge', 'caviz'], ['Stop Cafe', 'stop-cafe'], ['Water Garden', 'water-garden'],
    ['Lupin Coffee', 'lupin-coffee'], ['Best Place Baku', 'best-place'],
];

/** Photos of a project in the experience block: at most 4 (the gallery layouts in site.css); focus => object-position. */
const PROJECT_PHOTOS_MAX = 4;
const PHOTO_FOCUS = ['center' => '50% 50%', 'left' => '20% 50%', 'right' => '80% 50%', 'top' => '50% 20%', 'bottom' => '50% 80%'];

/** Projects of the experience block: [tag, text, photo caption, [[photo in /assets/img/experience/, focus], …]] */
const SEED_PROJECTS = [
    [
        ['az' => '200+ ölkə', 'ru' => '200+ стран', 'en' => '200+ countries'],
        [
            'az' => '200-dən çox ölkənin qatıldığı Taekvondo üzrə Dünya Çempionatında qida sektorunun keyfiyyətə nəzarət və tənzimləmə prosesinin birbaşa həyata keçirilməsi.',
            'ru' => 'Непосредственное ведение контроля качества и регулирования питания на Чемпионате мира по тхэквондо, в котором участвовали более 200 стран.',
            'en' => 'Directly running quality control and regulation of food services at the World Taekwondo Championships, with more than 200 participating countries.',
        ],
        ['az' => 'Bakı, 2023 · Taekvondo üzrə Dünya Çempionatı', 'ru' => 'Баку, 2023 · Чемпионат мира по тхэквондо', 'en' => 'Baku, 2023 · World Taekwondo Championships'],
        [['wtc-baku-2023.jpg', 'center'], ['wtc-team-arena.jpg', 'right'], ['wtc-team-stage.jpg', 'center'], ['wtc-team-banner.jpg', 'center']],
    ],
    [
        ['az' => 'AZ · KZ', 'ru' => 'AZ · KZ', 'en' => 'AZ · KZ'],
        [
            'az' => '**Beynəlxalq fəaliyyət:** Azərbaycanla yanaşı, Qazaxıstan kimi xarici bazarlarda da milli mətbəx layihələri ilə əməkdaşlıq.',
            'ru' => '**Международная работа:** помимо Азербайджана — сотрудничество с проектами национальной кухни на зарубежных рынках, например в Казахстане.',
            'en' => '**International work:** besides Azerbaijan, we collaborate with national cuisine projects in foreign markets such as Kazakhstan.',
        ],
        ['az' => '', 'ru' => '', 'en' => ''],
        [],
    ],
];

/** [question, answer]; every text is ['az' => …, 'ru' => …, 'en' => …] */
const SEED_FAQS = [
    [
        ['az' => 'Restoran auditi nədir?', 'ru' => 'Что такое аудит ресторана?', 'en' => 'What is a restaurant audit?'],
        [
            'az' => 'Restoran auditi — restoranın maliyyəsinin, maya dəyərinin, anbarının, satınalmasının və daxili nəzarət sisteminin müstəqil yoxlanılmasıdır. Audit gizli itkiləri, artan xərcləri və sui-istifadə risklərini üzə çıxarır, nəticədə isə konkret təkmilləşdirmə planı hazırlanır.',
            'ru' => 'Аудит ресторана — это независимая проверка финансов, себестоимости, склада, закупок и системы внутреннего контроля заведения. Он показывает скрытые потери, рост затрат и риски злоупотреблений, а по итогам готовится конкретный план улучшений.',
            'en' => 'A restaurant audit is an independent review of a venue’s finances, food costs, stock, purchasing and internal controls. It reveals hidden losses, rising costs and the risk of misconduct, and ends with a concrete improvement plan.',
        ],
    ],
    [
        ['az' => 'Restoran auditi zamanı nələri yoxlayırsınız?', 'ru' => 'Что вы проверяете при аудите ресторана?', 'en' => 'What do you check during a restaurant audit?'],
        [
            'az' => 'P&L hesabatını və pul axınını, hər yeməyin maya dəyərini (food cost) və texnoloji kartları, anbar qalıqlarını və sayımları, təchizatçı qiymətlərini, əməkhaqqı fondunu, həmçinin səlahiyyət bölgüsünü və gündəlik əməliyyatlara nəzarəti.',
            'ru' => 'P&L и денежный поток, себестоимость (фудкост) каждого блюда и технологические карты, складские остатки и инвентаризацию, цены поставщиков, фонд оплаты труда, а также распределение полномочий и контроль ежедневных операций.',
            'en' => 'The P&L and cash flow, the food cost of every dish and its recipe card, stock balances and counts, supplier prices, payroll, and the division of responsibilities and control of daily operations.',
        ],
    ],
    [
        ['az' => 'Restoran auditi kimə lazımdır?', 'ru' => 'Кому нужен аудит ресторана?', 'en' => 'Who needs a restaurant audit?'],
        [
            'az' => 'Gəliri artsa da mənfəəti azalan, maya dəyəri yüksələn, anbarında kəsir və ya artıq qalıq yaranan restoran, kafe və otellərə, həmçinin açılışa hazırlaşan yeni obyektlərə.',
            'ru' => 'Ресторанам, кафе и отелям, где при росте выручки падает прибыль, растёт себестоимость или на складе появляются недостачи и излишки, а также новым заведениям перед открытием.',
            'en' => 'Restaurants, cafés and hotels whose profit falls even as revenue grows, whose food costs keep rising or whose stock shows shortages and surpluses — and new venues getting ready to open.',
        ],
    ],
    [
        ['az' => 'Hansı müəssisələrlə işləyirsiniz?', 'ru' => 'С какими заведениями вы работаете?', 'en' => 'What kinds of businesses do you work with?'],
        [
            'az' => 'İaşə və restoran sektorunun kiçik və orta ölçülü müəssisələri ilə — restoran, kafe, lounge və otel brendləri. Həm sıfırdan qurulan yeni obyektlərə, həm də fəaliyyətdə olan restoranlara xidmət göstəririk.',
            'ru' => 'С малыми и средними заведениями общепита — ресторанами, кафе, лаунжами и отельными брендами. Работаем и с новыми заведениями, которые открываются с нуля, и с действующими ресторанами.',
            'en' => 'Small and mid-sized food service businesses — restaurants, cafés, lounges and hotel brands. We work both with new venues opening from scratch and with restaurants already in operation.',
        ],
    ],
    [
        ['az' => 'Hansı xidmətləri göstərirsiniz?', 'ru' => 'Какие услуги вы оказываете?', 'en' => 'What services do you offer?'],
        [
            'az' => 'Altı istiqamətdə: maliyyə və P&L, xərc analizi, xərclərin optimallaşdırılması, maya dəyəri və texnoloji kartlar, satınalma və inventar, daxili nəzarət və təhlükəsizlik.',
            'ru' => 'По шести направлениям: финансы и P&L, анализ затрат, оптимизация затрат, себестоимость и технологические карты, закупки и инвентаризация, внутренний контроль и безопасность.',
            'en' => 'Six areas: finance and P&L, cost analysis, cost optimization, food costing and recipe cards, purchasing and inventory, internal control and security.',
        ],
    ],
    [
        ['az' => 'Yeni restoran açırıq — necə kömək edə bilərsiniz?', 'ru' => 'Мы открываем новый ресторан — чем вы поможете?', 'en' => 'We’re opening a new restaurant — how can you help?'],
        [
            'az' => 'Böyük şəbəkələrin idarə olunmasında qazanılmış qabaqcıl təcrübə və standartları yeni obyektə uyğunlaşdırırıq: texnoloji kartlar, maya dəyəri, satınalma və uçot sistemi ilk gündən düzgün qurulur.',
            'ru' => 'Адаптируем к новому заведению передовой опыт и стандарты крупных сетей: технологические карты, себестоимость, закупки и система учёта выстраиваются правильно с первого дня.',
            'en' => 'We adapt the best practices and standards of large chains to your new venue, so recipe cards, food costing, purchasing and accounting are set up right from day one.',
        ],
    ],
    [
        ['az' => 'Azərbaycandan kənarda işləyirsiniz?', 'ru' => 'Вы работаете за пределами Азербайджана?', 'en' => 'Do you work outside Azerbaijan?'],
        [
            'az' => 'Bəli. Azərbaycanla yanaşı, Qazaxıstan kimi xarici bazarlarda da milli mətbəx layihələri ilə əməkdaşlıq edirik.',
            'ru' => 'Да. Помимо Азербайджана, мы сотрудничаем с проектами национальной кухни на зарубежных рынках, например в Казахстане.',
            'en' => 'Yes. Besides Azerbaijan, we work with national cuisine projects in foreign markets such as Kazakhstan.',
        ],
    ],
    [
        ['az' => 'Sizinlə necə əlaqə saxlaya bilərəm?', 'ru' => 'Как с вами связаться?', 'en' => 'How can I contact you?'],
        [
            'az' => '+994 55 855 61 52 nömrəsinə zəng edin, WhatsApp-da yazın və ya saytdakı formanı doldurun — sizinlə əlaqə saxlayacağıq.',
            'ru' => 'Позвоните по номеру +994 55 855 61 52, напишите в WhatsApp или заполните форму на сайте — мы с вами свяжемся.',
            'en' => 'Call +994 55 855 61 52, message us on WhatsApp or fill in the form on the website, and we will get back to you.',
        ],
    ],
];

/**
 * key => [menu name, SEO title, SEO description, page title (H1), meta keywords];
 * every text is ['az' => …, 'ru' => …, 'en' => …]. The home page takes its H1 from the hero texts.
 */
const SEED_PAGES = [
    'home' => [
        ['az' => 'Ana səhifə', 'ru' => 'Главная', 'en' => 'Home'],
        [
            'az' => 'Restoran auditi Bakıda — xərclərə nəzarət | Cost Control',
            'ru' => 'Аудит ресторана в Баку — контроль затрат | Cost Control',
            'en' => 'Restaurant audit in Baku — cost control | Cost Control',
        ],
        [
            'az' => 'Restoran auditi və xərclərə nəzarət: maya dəyəri (food cost), texnoloji kartlar, anbar sayımı, P&L və daxili nəzarət. Bakı, 2021-ci ildən, 18 brend.',
            'ru' => 'Аудит ресторана и контроль затрат: себестоимость и фудкост, технологические карты, инвентаризация, P&L и внутренний контроль. Баку, с 2021 года.',
            'en' => 'Restaurant audit and cost control: food cost, recipe cards, stock counts, P&L and internal control. Baku, since 2021, 18 partner brands.',
        ],
        ['az' => '', 'ru' => '', 'en' => ''],
        [
            'az' => 'restoran auditi, restoran auditi Bakı, restoranların auditi, xərclərə nəzarət, maya dəyəri, food cost, texnoloji kart, anbar sayımı, restoran konsaltinqi',
            'ru' => 'аудит ресторана, аудит ресторана Баку, аудит ресторанов, контроль затрат ресторана, себестоимость блюд, фудкост, технологические карты, инвентаризация, ресторанный консалтинг',
            'en' => 'restaurant audit, restaurant audit Baku, restaurant cost control, food cost, recipe cards, stock count, restaurant consulting Baku',
        ],
    ],
    'about' => [
        ['az' => 'Haqqımızda', 'ru' => 'О нас', 'en' => 'About us'],
        [
            'az' => 'Haqqımızda — restoran auditi komandası | Cost Control',
            'ru' => 'О компании — аудит ресторанов в Баку | Cost Control',
            'en' => 'About us — restaurant audit team in Baku | Cost Control',
        ],
        [
            'az' => 'Cost Control 2021-ci ildən Bakıda restoran auditi və xərclərə nəzarət aparır: böyük şəbəkələrin standartlarını kiçik və orta restoranlara uyğunlaşdırırıq.',
            'ru' => 'С 2021 года Cost Control проводит аудит ресторанов и контроль затрат в Баку: адаптируем стандарты крупных сетей для небольших и средних заведений.',
            'en' => 'Since 2021, Cost Control has run restaurant audits and cost control in Baku, adapting the standards of large chains to small and mid-sized venues.',
        ],
        [
            'az' => 'Cost Control — Bakıda restoran auditi komandası',
            'ru' => 'Cost Control — команда аудита ресторанов в Баку',
            'en' => 'Cost Control — a restaurant audit team in Baku',
        ],
        [
            'az' => 'restoran auditi, Cost Control, restoran konsaltinqi Bakı, xərclərə nəzarət, iaşə sektoru',
            'ru' => 'аудит ресторанов, Cost Control, ресторанный консалтинг Баку, контроль затрат, общепит',
            'en' => 'restaurant audit, Cost Control, restaurant consulting Baku, cost control, food service',
        ],
    ],
    'services' => [
        ['az' => 'Xidmətlər', 'ru' => 'Услуги', 'en' => 'Services'],
        [
            'az' => 'Restoran auditi xidmətləri: maya dəyəri, inventar, P&L',
            'ru' => 'Услуги аудита ресторана: фудкост, инвентаризация, P&L',
            'en' => 'Restaurant audit services: food cost, inventory, P&L',
        ],
        [
            'az' => 'Restoran auditi altı istiqamətdə: maliyyə və P&L, xərc analizi və optimallaşdırma, maya dəyəri və texnoloji kartlar, satınalma, inventar və daxili nəzarət.',
            'ru' => 'Аудит ресторана по шести направлениям: финансы и P&L, анализ и оптимизация затрат, себестоимость и техкарты, закупки, инвентаризация, внутренний контроль.',
            'en' => 'Restaurant audit in six areas: finance and P&L, cost analysis and optimization, food costing and recipe cards, purchasing, inventory and internal control.',
        ],
        [
            'az' => 'Restoran auditi və xərclərə nəzarət xidmətləri',
            'ru' => 'Услуги по аудиту ресторана и контролю затрат',
            'en' => 'Restaurant audit and cost control services',
        ],
        [
            'az' => 'restoran auditi xidmətləri, maya dəyərinin hesablanması, food cost, texnoloji kartların hazırlanması, anbar inventarizasiyası, P&L hesabatı, daxili nəzarət',
            'ru' => 'услуги аудита ресторана, расчёт себестоимости блюд, фудкост, разработка технологических карт, инвентаризация в ресторане, P&L ресторана, внутренний контроль',
            'en' => 'restaurant audit services, food cost calculation, recipe cards, restaurant inventory, restaurant P&L, internal control',
        ],
    ],
    'clients' => [
        ['az' => 'Müştərilər', 'ru' => 'Клиенты', 'en' => 'Clients'],
        [
            'az' => 'Müştərilərimiz — restoran, kafe və otellər | Cost Control',
            'ru' => 'Наши клиенты — рестораны, кафе и отели | Cost Control',
            'en' => 'Our clients — restaurants, cafés and hotels | Cost Control',
        ],
        [
            'az' => 'Restoran auditi və xərclərə nəzarət üzrə əməkdaşlıq etdiyimiz 18 brend: Bakıda restoran, kafe, lounge və otellər.',
            'ru' => '18 брендов, с которыми мы работаем по аудиту ресторана и контролю затрат: рестораны, кафе, лаунжи и отели Баку.',
            'en' => '18 brands we have worked with on restaurant audits and cost control: restaurants, cafés, lounges and hotels in Baku.',
        ],
        [
            'az' => 'Müştərilərimiz: restoranlar, kafelər və otellər',
            'ru' => 'Наши клиенты: рестораны, кафе и отели',
            'en' => 'Our clients: restaurants, cafés and hotels',
        ],
        [
            'az' => 'restoran auditi, Bakı restoranları, kafe auditi, otel restoranının auditi',
            'ru' => 'аудит ресторана, рестораны Баку, аудит кафе, аудит ресторана отеля',
            'en' => 'restaurant audit, Baku restaurants, café audit, hotel restaurant audit',
        ],
    ],
    'faq' => [
        ['az' => 'Suallar', 'ru' => 'Вопросы', 'en' => 'FAQ'],
        [
            'az' => 'Restoran auditi haqqında suallar və cavablar | Cost Control',
            'ru' => 'Аудит ресторана: вопросы и ответы | Cost Control',
            'en' => 'Restaurant audit: questions and answers | Cost Control',
        ],
        [
            'az' => 'Restoran auditi nədir, nələri yoxlayırıq, kimə lazımdır və necə müraciət etmək olar — xərclərə nəzarət haqqında tez-tez verilən suallar.',
            'ru' => 'Что такое аудит ресторана, что мы проверяем, кому он нужен и как оставить заявку — частые вопросы о контроле затрат.',
            'en' => 'What a restaurant audit is, what we check, who needs one and how to get in touch — frequently asked questions about cost control.',
        ],
        [
            'az' => 'Restoran auditi haqqında suallar',
            'ru' => 'Вопросы об аудите ресторана',
            'en' => 'Restaurant audit FAQ',
        ],
        [
            'az' => 'restoran auditi nədir, restoran auditi necə aparılır, restoranın yoxlanılması, xərclərə nəzarət',
            'ru' => 'что такое аудит ресторана, как проводится аудит ресторана, проверка ресторана, контроль затрат',
            'en' => 'what is a restaurant audit, how a restaurant audit works, restaurant inspection, cost control',
        ],
    ],
    'blog' => [
        ['az' => 'Bloq', 'ru' => 'Блог', 'en' => 'Blog'],
        [
            'az' => 'Bloq: restoran auditi və food cost haqqında | Cost Control',
            'ru' => 'Блог об аудите ресторанов и контроле затрат | Cost Control',
            'en' => 'Blog on restaurant audits and cost control | Cost Control',
        ],
        [
            'az' => 'Restoran auditi, maya dəyəri (food cost), anbar sayımı və restoran maliyyəsi haqqında praktiki məqalələr — Cost Control komandasından.',
            'ru' => 'Практические статьи об аудите ресторанов, себестоимости и фудкосте, инвентаризации и финансах ресторана — от команды Cost Control.',
            'en' => 'Practical articles on restaurant audits, food cost, stock counts and restaurant finance from the Cost Control team.',
        ],
        [
            'az' => 'Bloq: restoran auditi və xərclərə nəzarət',
            'ru' => 'Блог об аудите ресторанов и контроле затрат',
            'en' => 'Blog: restaurant audits and cost control',
        ],
        [
            'az' => 'restoran auditi, restoran biznesi üçün məsləhətlər, food cost, maya dəyəri, restoran maliyyəsi',
            'ru' => 'аудит ресторана, советы ресторатору, фудкост, себестоимость блюд, финансы ресторана',
            'en' => 'restaurant audit, restaurant business tips, food cost, restaurant finance',
        ],
    ],
    'contact' => [
        ['az' => 'Əlaqə', 'ru' => 'Контакты', 'en' => 'Contact'],
        [
            'az' => 'Əlaqə — restoran auditi sifariş edin | Cost Control, Bakı',
            'ru' => 'Контакты — заказать аудит ресторана в Баку | Cost Control',
            'en' => 'Contact — order a restaurant audit in Baku | Cost Control',
        ],
        [
            'az' => 'Restoran auditi sifariş etmək üçün: +994 55 855 61 52, WhatsApp və ya sayt forması. Bakı, Nizami rayonu, Heydər Əliyev prospekti 5.',
            'ru' => 'Заказать аудит ресторана: +994 55 855 61 52, WhatsApp или форма на сайте. Баку, Низаминский район, проспект Гейдара Алиева, 5.',
            'en' => 'Order a restaurant audit: +994 55 855 61 52, WhatsApp or the form on this site. 5 Heydar Aliyev Avenue, Nizami district, Baku.',
        ],
        [
            'az' => 'Restoran auditi sifariş edin',
            'ru' => 'Заказать аудит ресторана',
            'en' => 'Order a restaurant audit',
        ],
        [
            'az' => 'restoran auditi sifarişi, restoran auditi Bakı, Cost Control əlaqə',
            'ru' => 'заказать аудит ресторана, аудит ресторана Баку, Cost Control контакты',
            'en' => 'order a restaurant audit, restaurant audit Baku, Cost Control contact',
        ],
    ],
];

<?php
/**
 * Seed content written by schema v1/v2, before the site was built on design V1.
 * A value that still equals its seed was never edited in the admin, so the v3
 * migration may replace it with the V1 text and its translations (see db.php).
 */

declare(strict_types=1);
defined('CCS') || exit;

return [
    // SEO texts that v2 copied to the home page
    'home_seo' => [
        'Cost Control Services — restoranlar üçün xərclərə nəzarət | Bakı',
        'Cost Control Services — iaşə və restoran sektoru üçün maliyyə və P&L, xərc analizi, maya dəyəri və texnoloji kartlar, satınalma, inventar və daxili nəzarət xidmətləri. Bakı.',
    ],
    'texts' => [
      'hero.eyebrow' => 'İaşə və restoran sektoru üçün xərclərə nəzarət',
      'hero.title' => 'Restoranınızın hər qəpiyi nəzarət altında',
      'hero.lead' => 'Restoran biznesində uğurun sirri təkcə dadlı yemək deyil. Hər bir xammalın, resursun və maliyyə axınının dəqiq uçotunu qurur, gizli israfları və boşluqları dərhal aşkarlayırıq.',
      'hero.cta' => 'Konsultasiya al',
      'about.title' => 'Biz kimik?',
      'about.p1' => '"Cost Control" şirkəti post-pandemiya dövrünün çətin və dinamik reallıqları fonunda — 2021-ci ilin sonlarından etibarən iaşə və restoran sektorunda peşəkar idarəetmə boşluqlarını doldurmaq məqsədilə təsis edilmişdir.',
      'about.p2' => 'Restoran biznesində uğurun sirri təkcə dadlı yemək deyil, hər bir xammalın, resursun və maliyyə axınının dəqiq uçotundan asılıdır. Cost Control mexanizmlərimiz sayəsində biznesdəki gizli israflar, artan maya dəyəri və ehtiyatların idarə olunmasındakı boşluqlar dərhal aşkarlanır.',
      'about.p3' => 'Böyük şəbəkələrin idarə olunmasında qazanılmış qabaqcıl təcrübələr və standartlar kiçik və orta ölçülü restoranlara uyğunlaşdırılır. Bu yanaşma həm yerli bazarda yeni obyektlərin sıfırdan qurulmasına münbit şərait yaradır, həm də mövcud restoranların gəlirliliyini maksimum səviyyəyə qaldırır.',
      'services.title' => 'Xidmətlərimiz',
      'services.lead' => 'Maliyyə uçotundan anbar sayımlarına qədər restoranın bütün xərc zəncirini şəffaf və idarə olunan edirik.',
      'clients.title' => 'Çalışdığımız şirkətlər',
      'clients.lead' => 'Restoran, kafe, lounge və otel brendləri ilə əməkdaşlıq edirik.',
      'experience.title' => 'Beynəlxalq və xüsusi təcrübə',
      'experience.p1' => '200-dən çox ölkənin qatıldığı Taekvondo üzrə Dünya Çempionatında qida sektorunun keyfiyyətə nəzarət və tənzimləmə prosesinin birbaşa həyata keçirilməsi.',
      'experience.p2' => 'Beynəlxalq fəaliyyət: Azərbaycanla yanaşı, Qazaxıstan kimi xarici bazarlarda da milli mətbəx layihələri ilə əməkdaşlıq.',
      'contact.title' => 'Bizimlə əlaqə',
      'contact.lead' => 'Müraciətinizi qoyun — mütəxəssisimiz sizinlə əlaqə saxlayacaq və restoranınız üçün uyğun həlli müzakirə edəcək.',
      'contact.address' => 'Bakı şəhəri, Nizami rayonu, Heydər Əliyev prospekti 5',
      'contact.success' => 'Təşəkkür edirik! Müraciətiniz qəbul olundu. Tezliklə sizinlə əlaqə saxlayacağıq.',
      'instagram.text' => 'Restoran biznesi üçün yeni videolar və praktiki məsləhətlər — mütəmadi olaraq Instagram səhifəmizdə.',
      'footer.text' => 'Restoran və iaşə biznesi üçün xərclərə nəzarət, maya dəyəri və maliyyə şəffaflığı.',
      'seo.title' => 'Cost Control Services — restoranlar üçün xərclərə nəzarət | Bakı',
      'seo.description' => 'Cost Control Services — iaşə və restoran sektoru üçün maliyyə və P&L, xərc analizi, maya dəyəri və texnoloji kartlar, satınalma, inventar və daxili nəzarət xidmətləri. Bakı.',
    ],
    'services' => [
      'Maliyyə və P&L' => [
        'lead' => 'Restoranın maliyyəsini şəffaf, rəqəmləri isə qərar qəbul etmək üçün aydın edirik.',
        'points' => [
          'Şəffaf Maliyyə Sistemi',
          'Aylıq P&L (Mənfəət və Zərər) Hesabatları',
          'Pul Axınının İdarə Edilməsi',
        ],
      ],
      'Xərc analizi' => [
        'lead' => 'Pulun harada itdiyini tapır və hər xərcin nəticəsini ölçürük.',
        'points' => [
          'İtkilərin Aşkar Edilməsi',
          'Xərclərin İzlənməsi',
          'Effektivlik Təhlili',
        ],
      ],
      'Xərclərin optimallaşdırılması' => [
        'lead' => 'Keyfiyyəti qoruyaraq xərcləri bazar və iş yükü ilə uyğunlaşdırırıq.',
        'points' => [
          'Bazar Xərclərinin Tənzimlənməsi',
          'Əməkhaqqı Fondu Optimallaşdırılması',
          'Kommunal və Digər Xərclər',
        ],
      ],
      'Maya dəyəri və texnoloji kartlar' => [
        'lead' => 'Hər yeməyin real maya dəyərini və düzgün satış qiymətini müəyyən edirik.',
        'points' => [
          'Texnoloji Kartların Hazırlanması',
          'Maya Dəyərinin Hesablanması',
          'Satış Qiymətlərinin Tənzimlənməsi',
        ],
      ],
      'Satınalma və inventar' => [
        'lead' => 'Satınalmanı sərfəli, anbarı isə nəzarətli edirik.',
        'points' => [
          'Sərfəli Təchizat',
          'Aylıq Peşəkar Sayımlar',
          'Artıq Ehtiyatların Qarşısının Alınması',
        ],
      ],
      'Daxili nəzarət və təhlükəsizlik' => [
        'lead' => 'Məsuliyyəti bölüşdürür, sui-istifadənin qarşısını erkən alırıq.',
        'points' => [
          'Səlahiyyət Bölgüsü',
          'Sui-istifadənin Qarşısının Alınması',
          'Nəzarət Mexanizmləri',
        ],
      ],
    ],
    'partners' => [
      'Vasilchuki',
      'Baribal',
      'Vətən',
      'The Real McCoy',
      'Sess restobar',
      'Green City Resort & Residence',
      'Şirvanşah muzey restoran',
      'Monkey Pub',
      'Xarı Bülbül',
      'Gondola cafe & lounge',
      'Pivnaya Apteka',
      'Manzara',
      'Resant Hotels & Restaurants',
      'Caviz Tea & Lounge',
      'Stop Cafe',
      'Water Garden',
      'Lupin Coffee',
      'Best Place Baku',
    ],
];

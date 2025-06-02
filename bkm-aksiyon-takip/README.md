# BKM Aksiyon Takip WordPress Plugin

## 📋 Proje Hakkında

BKM Aksiyon Takip, WordPress tabanlı kapsamlı bir aksiyon ve görev yönetim sistemidir. Bu plugin ile organizasyonlardaki aksiyonları, görevleri ve performansları takip edebilir, raporlayabilirsiniz.

## 🚀 Özellikler

### Phase 2 - Tamamlanan Özellikler ✅

#### Admin Panel Modülü
- **Aksiyon Yönetimi**
  - Tüm aksiyonları listeleme
  - Yeni aksiyon ekleme
  - Aksiyon düzenleme/silme
  - Detaylı form validasyonu
  - Progress bar ile ilerleme takibi

- **Kategori Yönetimi**
  - Kategori ekleme/düzenleme/silme
  - Örnek kategoriler (Kalite, İş Güvenliği, Çevre, vb.)

- **Performans Yönetimi**
  - Performans seviyesi tanımlama
  - Örnek performans seviyeleri (Mükemmel, İyi, Orta, Zayıf)

- **Raporlar**
  - Genel istatistikler
  - Kategori bazında dağılım
  - Performans analizi
  - Aylık trend raporları
  - İlerleme durumu dağılımı

#### Teknik Özellikler
- **Güvenlik**
  - WordPress nonce kullanımı
  - Input sanitization
  - SQL injection koruması
  - Kullanıcı yetki kontrolü

- **UI/UX**
  - Responsive tasarım
  - Modern admin arayüzü
  - Progress bar animasyonları
  - AJAX destekli formlar
  - Kullanıcı dostu hata mesajları

- **Veritabanı**
  - Optimize edilmiş tablo yapısı
  - Index'ler ile hızlı sorgular
  - Örnek veriler ile başlangıç

## 🗄️ Veritabanı Yapısı

### Tablolar
1. **wp_bkm_aksiyonlar** - Ana aksiyon tablosu
2. **wp_bkm_kategoriler** - Kategori master tablosu
3. **wp_bkm_performanslar** - Performans master tablosu
4. **wp_bkm_gorevler** - Görev tablosu (aksiyon ile ilişkili)

### Aksiyon Tablosu Alanları
- Sıra No (otomatik)
- Aksiyonu Tanımlayan (WordPress kullanıcıları)
- Önem Derecesi (1-3)
- Açılma Tarihi
- Hafta No
- Kategori
- Aksiyon Sorumlusu
- Tespit Nedeni
- Aksiyon Açıklaması
- Hedef Tarih
- Kapanma Tarihi
- Performans
- İlerleme Durumu (%)
- Notlar

## 📁 Dosya Yapısı

```
bkm-aksiyon-takip/
├── bkm-aksiyon-takip.php          # Ana plugin dosyası
├── includes/
│   ├── class-activator.php        # Plugin aktivasyon
│   ├── class-deactivator.php      # Plugin deaktivasyon
│   ├── class-database.php         # Veritabanı işlemleri
│   ├── class-admin.php           # Admin panel yönetimi
│   ├── class-frontend.php        # Frontend işlemleri
│   └── class-shortcode.php       # Shortcode yönetimi
├── admin/
│   ├── css/admin.css             # Admin panel stilleri
│   ├── js/admin.js               # Admin panel JavaScript
│   └── views/
│       ├── aksiyon-list.php      # Aksiyon listesi
│       ├── aksiyon-form.php      # Aksiyon ekleme/düzenleme
│       ├── kategori-list.php     # Kategori yönetimi
│       ├── performans-list.php   # Performans yönetimi
│       └── raporlar.php          # Raporlar sayfası
├── public/
│   ├── css/
│   ├── js/
│   └── views/
└── README.md
```

## 🔧 Kurulum

1. Plugin dosyalarını `/wp-content/plugins/bkm-aksiyon-takip/` klasörüne yükleyin
2. WordPress admin panelinden Pluginler > Yüklü Pluginler bölümüne gidin
3. "BKM Aksiyon Takip" pluginini aktif edin
4. Aktivasyon sırasında veritabanı tabloları otomatik oluşturulur
5. Admin panelde "BKM Aksiyonlar" menüsü görünür

## 📊 Kullanım

### Admin Panel
1. **Aksiyon Ekleme**: Admin Panel > BKM Aksiyonlar > Aksiyon Ekle
2. **Kategori Yönetimi**: Admin Panel > BKM Aksiyonlar > Kategoriler
3. **Performans Yönetimi**: Admin Panel > BKM Aksiyonlar > Performanslar
4. **Raporlar**: Admin Panel > BKM Aksiyonlar > Raporlar

### Örnek Veriler
Plugin aktivasyonu sırasında aşağıdaki örnek veriler eklenir:

**Kategoriler:**
- Kalite Yönetimi
- İş Güvenliği
- Çevre Yönetimi
- Bilgi Güvenliği
- İnsan Kaynakları
- Süreç İyileştirme
- Müşteri Memnuniyeti
- Teknoloji

**Performans Seviyeleri:**
- Mükemmel
- İyi
- Orta
- Zayıf
- Beklemede

## 🚧 Gelecek Sürümler (Phase 3-4)

### Phase 3: Frontend Modülü
- [ ] Shortcode sistemi (`[aksiyon_takipx]`)
- [ ] Kullanıcı kimlik doğrulama
- [ ] Kullanıcıya özel aksiyon listesi
- [ ] Görev yönetim sistemi
- [ ] Yetki kontrolü (Ekleme/Düzenleme/Silme)

### Phase 4: İyileştirmeler
- [ ] Gelişmiş raporlar
- [ ] Chart.js entegrasyonu
- [ ] E-posta bildirimleri
- [ ] Excel export/import
- [ ] Dashboard widget'ları
- [ ] Performance optimizasyonları

## 🔧 Geliştirici Notları

### WordPress Coding Standards
- PSR-4 autoloading uyumlu
- WordPress coding standards
- Güvenli veritabanı işlemleri
- Çoklu dil desteği hazır

### Özelleştirme
Plugin modüler yapıda tasarlanmıştır. Yeni özellikler kolayca eklenebilir:

```php
// Yeni hook'lar
do_action('bkm_before_aksiyon_save', $data);
do_action('bkm_after_aksiyon_save', $aksiyon_id);

// Yeni filter'lar
$data = apply_filters('bkm_aksiyon_data', $data);
```

## 📝 Değişiklik Notları

### v1.0.0 (Phase 2 Tamamlandı)
- ✅ Admin panel tam fonksiyonel
- ✅ CRUD işlemleri tamamlandı
- ✅ Raporlar eklendi
- ✅ Responsive tasarım
- ✅ Form validasyonları
- ✅ Örnek veriler

## 👥 Katkıda Bulunma

1. Repository'yi fork edin
2. Feature branch oluşturun (`git checkout -b feature/yeni-ozellik`)
3. Commit'lerinizi yapın (`git commit -am 'Yeni özellik eklendi'`)
4. Branch'inizi push edin (`git push origin feature/yeni-ozellik`)
5. Pull Request oluşturun

## 📄 Lisans

Bu proje MIT lisansı altında lisanslanmıştır.

## 📞 İletişim

**Geliştirici:** @onurgezer1  
**Oluşturulma Tarihi:** 2025-06-02  
**Son Güncelleme:** 2025-06-02

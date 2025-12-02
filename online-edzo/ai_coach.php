<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}
require_once 'db.php';
include_once 'premium_check.php';

$user_id = $_SESSION['user_id'];
$is_premium = isPremium($user_id);

// Prémium ellenőrzés - csak prémium felhasználóknak
if (!$is_premium) {
  header('Location: upgrade.php?reason=ai_coach');
  exit;
}

// Chat üzenetek lekérése
$stmt = $pdo->prepare("SELECT * FROM ai_chat_messages WHERE user_id = ? ORDER BY created_at ASC LIMIT 100");
$stmt->execute([$user_id]);
$messages = $stmt->fetchAll();

// Új üzenet küldése
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_message'])) {
  $user_message = trim($_POST['user_message'] ?? '');
  
  if ($user_message) {
    // Felhasználó üzenetének mentése
    $stmt = $pdo->prepare("INSERT INTO ai_chat_messages (user_id, role, message) VALUES (?, 'user', ?)");
    $stmt->execute([$user_id, $user_message]);
    
    // AI válasz generálása (egyszerű mock válaszok - valós AI integrációhoz API kulcs kell)
    $ai_response = generateAIResponse($user_message);
    
    // AI válasz mentése
    $stmt = $pdo->prepare("INSERT INTO ai_chat_messages (user_id, role, message) VALUES (?, 'assistant', ?)");
    $stmt->execute([$user_id, $ai_response]);
    
    // Redirect to prevent form resubmission
    header('Location: ai_coach.php');
    exit;
  }
}

// Chat history törlése
if (isset($_GET['clear_chat'])) {
  $stmt = $pdo->prepare("DELETE FROM ai_chat_messages WHERE user_id = ?");
  $stmt->execute([$user_id]);
  header('Location: ai_coach.php');
  exit;
}

// AI válasz generáló függvény - INTELLIGENS CONTEXTUS ALAPÚ VÁLASZOK
function generateAIResponse($message) {
  $message_lower = mb_strtolower($message, 'UTF-8');
  $message_clean = trim($message_lower);
  
  // 1. ÜDVÖZLÉS ÉS ALAPVETŐ INTERAKCIÓK
  if (preg_match('/^(szia|sziasztok|hello|helló|hi|hey|üdv|jónapot|jó napot|szerusz)/i', $message_clean)) {
    return "Szia! 👋 Én vagyok a személyes AI Edződ!\n\nSzeretném megismerni a céljaidat és segíteni neked. Kérdezz bármit:\n\n💪 Edzéstervek és gyakorlatok\n🍎 Táplálkozás és diéta\n📊 Haladás követés\n🎯 Célok elérése\n💡 Motiváció\n\nMiben segíthetek ma? Írj bátran úgy, mintha egy igazi edzővel beszélgetnél!";
  }
  
  if (preg_match('/köszön|kösz|thanks|thx|ty/i', $message_clean)) {
    return "Szívesen! 😊 Mindig örülök, ha segíthetek!\n\nHa bármi más kérdés merül fel az edzéssel, táplálkozással vagy a céljaid elérésével kapcsolatban, bátran írj! Itt vagyok neked. 💪";
  }
  
  if (preg_match('/hogy vagy|mi újság|hogy megy|mi van veled/i', $message_clean)) {
    return "Köszönöm, jól vagyok! 🤖 Készen állok, hogy segítsek neked az edzéseidben!\n\nÉs te? Hogy állsz a céljaid megvalósításával? Van valami, amiben ma segíthetek?";
  }
  
  // 2. EDZÉSTERVEK - RÉSZLETES KONTEXTUÁLIS VÁLASZOK
  if (preg_match('/edzésterv|terv|program|miből kezdjem|hogyan kezdjem|edzés.*kezd/i', $message_clean)) {
    if (preg_match('/kezdő|újonc|most kezd|kezdem|soha|először/i', $message_clean)) {
      return "Remek, hogy elkezded! 🎉 Kezdőknek fontos a fokozatosság.\n\n**Kezdő edzésterv (3x/hét):**\n\n📅 **Hétfő, Szerda, Péntek - Teljes test edzés:**\n1. Guggolás - 3x10\n2. Fekvenyomás vagy fekvőtámasz - 3x10\n3. Evezés vagy húzódzkodás segítséggel - 3x10\n4. Váll nyomás - 3x10\n5. Plank - 3x30mp\n\n**Fontos szabályok:**\n✅ Technika a legfontosabb!\n✅ Kezdj könnyű súlyokkal\n✅ Pihenő 60-90mp sorozatok között\n✅ Melegítés 5-10 perc\n\nMilyen eszközökhöz van hozzáférésed? (edzőterem/otthon/szabadsúly)";
    }
    
    if (preg_match('/izom|tömeg|bulk|épít|gyarapít|nagyobb/i', $message_clean)) {
      return "Izomépítés - remek cél! 💪 Ehhez jó edzésterv és megfelelő táplálkozás kell.\n\n**Izomépítő terv (4-5x/hét):**\n\n📅 **Split edzések:**\n- **Hétfő:** Mell + Tricepsz\n- **Kedd:** Hát + Bicepsz  \n- **Szerda:** Pihenő\n- **Csütörtök:** Láb\n- **Péntek:** Váll + Has\n\n**Fontos szabályok:**\n✅ 8-12 ismétlés/sorozat\n✅ 3-4 sorozat/gyakorlat\n✅ Alapgyakorlatok (guggolás, fekvenyomás, húzódzkodás, evezés)\n✅ Progresszív túlterhelés (fokozatosan nehezebb súlyok)\n\n**Táplálkozás:** +300-500 kcal, 2g fehérje/ttkg\n\nMennyi ideje edzel? Milyen szinten vagy?";
    }
    
    if (preg_match('/fogy|lefogy|zsír|diet|karcsú|vékony|súly.*le|leadni/i', $message_clean)) {
      return "Fogyás - kitűnő cél! 🔥 A kulcs: kalóriadeficit + edzés.\n\n**Zsírégető terv (4-5x/hét):**\n\n📅 **Kombináció:**\n- **Hétfő, Szerda, Péntek:** Erősítő edzés (teljes test)\n- **Kedd, Csütörtök:** HIIT vagy kardió (30 perc)\n\n**Erősítő edzés mintaterv:**\n1. Guggolás - 4x12\n2. Fekvenyomás - 4x12\n3. Evezés - 4x12\n4. Kitörés - 3x15\n5. Plank - 3x1 perc\n\n**HIIT példa:**\n- 30mp sprintelés\n- 30mp pihenő\n- Ismételd 15-20x\n\n**Táplálkozás:** -300-500 kcal deficitben, 2g fehérje/ttkg (izomvédelem)\n\nMennyi súlyt szeretnél leadni? Hány kalóriát eszel most naponta?";
    }
    
    return "Szívesen segítek edzésterv összeállításában! 📋\n\nAhhoz, hogy a legjobb tervet tudjam adni, mondd el:\n\n1. **Mi a fő célod?**\n   - Izomépítés\n   - Fogyás\n   - Erőnövelés\n   - Állóképesség\n   - Általános fitnesz\n\n2. **Hány napot tudsz edzeni hetente?**\n\n3. **Milyen szinten vagy?** (kezdő/haladó/profi)\n\n4. **Hol edzel?** (edzőterem/otthon)\n\n5. **Van sérülésed vagy korlátozásod?**\n\nVálaszolj ezekre, és összeállítok neked egy személyre szabott tervet!";
  }
  
  // 3. TÁPLÁLKOZÁS - RÉSZLETES TANÁCSOK
  if (preg_match('/táplálkozás|étrend|étkezés|étel|kaja|enni|kalória|makró|fehérje|szénhidrát|zsír/i', $message_clean)) {
    if (preg_match('/kalória|kcal|mennyit.*enni|mennyit.*kell/i', $message_clean)) {
      return "A kalóriaszükséglet számítása alapvető! 📊\n\n**Alapanyagcsere (BMR) számítás:**\n- **Férfi:** 10 × súly(kg) + 6.25 × magasság(cm) - 5 × kor + 5\n- **Nő:** 10 × súly(kg) + 6.25 × magasság(cm) - 5 × kor - 161\n\n**Napi kalóriaszükséglet (TDEE):**\nBMR × aktivitási szorzó:\n- Ülő munka: 1.2\n- Kis aktivitás (1-3x edzés/hét): 1.375\n- Közepes (3-5x edzés/hét): 1.55\n- Nagy aktivitás (6-7x edzés/hét): 1.725\n- Profi sportoló: 1.9\n\n**Célok:**\n🔥 Fogyás: TDEE - 300-500 kcal\n💪 Izomépítés: TDEE + 300-500 kcal\n⚖️ Tartás: TDEE\n\n**Add meg az adataidat és kiszámolom:**\n- Mennyi a testsúlyod (kg)?\n- Magasság (cm)?\n- Életkor?\n- Aktivitási szint?";
    }
    
    if (preg_match('/fehérje|protein/i', $message_clean)) {
      return "A fehérje az izomépítés alapja! 🥩\n\n**Fehérjeszükséglet:**\n- **Kezdő:** 1.4-1.6 g/testsúly kg\n- **Haladó:** 1.6-2.0 g/testsúly kg\n- **Versenyző/intenzív:** 2.0-2.5 g/testsúly kg\n\n**Legjobb fehérjeforrások:**\n🍗 Csirkemell (31g/100g)\n🥚 Tojás (13g/100g)\n🐟 Tonhal (30g/100g)\n🥛 Túró (18g/100g)\n🥩 Marhahús (26g/100g)\n🫘 Lencse (9g/100g)\n🥜 Mandula (21g/100g)\n\n**Protein por:**\n- Csak akkor, ha nem éred el étellel\n- Whey: edzés után (gyors)\n- Kazein: este lefekvés előtt (lassú)\n\n**Példa napi beosztás (80kg sportoló, 160g fehérje):**\n- Reggeli: 4 tojás (26g)\n- Tízórai: Protein shake (30g)\n- Ebéd: 200g csirke (62g)\n- Uzsonna: Túró 200g (36g)\n- Vacsora: Hal 150g (30g)\n\nMennyi a testsúlyod? Számoljuk ki a szükségleted!";
    }
    
    return "A táplálkozás az eredmények 70%-a! 🍎\n\n**Mire vagy kíváncsi?**\n\n1. **Kalóriaszükséglet** - Mennyit egyél?\n2. **Makrók** - Fehérje, szénhidrát, zsír arány\n3. **Étkezési időzítés** - Mikor egyél?\n4. **Étrend összeállítás** - Mit egyél?\n5. **Kiegészítők** - Kell-e protein, kreatin?\n\nKérdezz bátran konkrétan!";
  }
  
  // 4. GYAKORLATOK ÉS TECHNIKA
  if (preg_match('/fekvenyomás|bench press|nyomás/i', $message_clean)) {
    return "Fekvenyomás - a melledzés királya! 🏋️\n\n**Helyes technika:**\n1. **Pozíció:** Feküdj a padra, szemek a rúd alá\n2. **Láb:** Stabilan a földön, nyomás a sarkakkal\n3. **Lapockák:** Húzd össze és le a hátad mögé\n4. **Fogás:** Kézfej szélessége kb. vállszélesség + 10-15cm\n5. **Mozgás:** Ereszd kontrolláltan a mellkas aljára/közepére\n6. **Könyök:** 45°-os szög a testtel (NE 90°!)\n7. **Nyomás:** Robbanékony felfelé\n\n**Gyakori hibák:**\n❌ Fenék felemelése\n❌ Pattogás a mellkason\n❌ Könyök túl széles (90° = váll sérülés!)\n❌ Lapockák nem szorítva\n\n**Progresszió:**\n- Kezdő: 3x10\n- Haladó: 4x8\n- Erő: 5x5\n\nHol akadsz el a mozdulatban? Alsó/közép/felső szakasz?";
  }
  
  if (preg_match('/guggolás|squat|láb/i', $message_clean)) {
    return "Guggolás - a lábedzés alapja! 🦵\n\n**Helyes technika:**\n1. **Lábállás:** Vállszélesség vagy kicsit szélesebb\n2. **Lábfej:** Kicsit kifelé fordítva (10-15°)\n3. **Rúd:** Trapéz felső részén (high bar) vagy alján (low bar)\n4. **Mozgás:** Fenék hátra-le, mintha leülnél\n5. **Térd:** Lábujj irányába mozog, NE menjen túl rajta!\n6. **Mélység:** Combcsont vízszintesig vagy mélyebbre\n7. **Hát:** MINDIG egyenes!\n8. **Tekintet:** Előre, kissé lefelé\n\n**Gyakori hibák:**\n❌ Térd túl előre\n❌ Hát lekerekítése\n❌ Sarok felemelkedése\n❌ Gyors, kontrollálatlan mozgás\n\n**Ha nehéz:**\n- Mobilitás: boka + csípő nyújtás\n- Goblet squat súlyzóval\n- Box squat (ülj le padra)\n\n**Variációk:**\n- Front squat (mellső rúdtartás)\n- Bulgarian split squat\n- Goblet squat\n\nMi a konkrét problémád a guggolással?";
  }
  
  if (preg_match('/húzódzkodás|pull.*up|chin.*up|pullup|hát.*gyakorlat/i', $message_clean)) {
    return "Húzódzkodás - a hátedzés csúcsa! 💪\n\n**Helyes technika:**\n1. **Fogás:** Széles (széles hát) vagy szűk (vastagság)\n2. **Kiindulás:** Teljes kifeszítés, lapockák le\n3. **Húzás:** Lapockákkal húzz, könyökkel vezetni\n4. **Csúcspont:** Áll a rúd fölé vagy mellkas érintse\n5. **Leereszkedés:** Kontrolláltan, teljes kifeszítésig\n\n**Ha még nem megy:**\n\n**1. Negatív húzódzkodás (4 hét):**\n- Ugorj fel segítséggel\n- Ereszkedj le LASSAN (5 mp)\n- 3-4 sorozat, 3-5 ismétlés\n- Heti 3x\n\n**2. Gumiszalag segítség:**\n- Térdre/lábra gumiszalag\n- Normál húzódzkodás\n- Fokozatosan gyengébb szalag\n\n**3. Ausztrál húzódzkodás:**\n- Alacsony rúd\n- Testszöge: minél vízszintesebb = nehezebb\n- 4x8-12 ismétlés\n\n**Erősítő gyakorlatok:**\n- Lehúzás gépen\n- Evezés (hát vastagság)\n- Bicepsz (segédizom)\n\nHány darabot tudsz most? Segítek a fejlődésben!";
  }
  
  // 5. CÉLOK ÉS HALADÁS
  if (preg_match('/cél|target|el.*akar|szeretné|mennyi.*idő|mikor.*eredmény/i', $message_clean)) {
    return "A célkitűzés kulcsfontosságú! 🎯\n\n**SMART célok módszere:**\n\n**S - Specific (Konkrét)**\n❌ \"Le akarok fogyni\"\n✅ \"5 kg-ot akarok leadni\"\n\n**M - Measurable (Mérhető)**\n✅ Testsúly, körméretek, teljesítmény\n\n**A - Achievable (Elérhető)**\n❌ 20 kg 1 hónap alatt\n✅ 0.5-1 kg/hét fogyás (egészséges)\n\n**R - Relevant (Releváns)**\n✅ Fontos neked, nem mások miatt\n\n**T - Time-bound (Időkorlát)**\n✅ 12 hét alatt 6 kg fogyás\n\n**Reális elvárások:**\n\n**Fogyás:**\n- 0.5-1 kg/hét egészséges\n- 3-6 hónap látható változás\n\n**Izomépítés (férfi):**\n- Kezdő: 1-1.5 kg izom/hó\n- Haladó: 0.5-1 kg izom/hó\n- Profi: 0.25-0.5 kg izom/hó\n\n**Erőnövelés:**\n- Kezdő: havi 10-15% növekedés\n- Haladó: havi 2-5% növekedés\n\n**Mi a TE célod? Mondd el részletesen és segítek megtervezni!**";
  }
  
  // 6. MOTIVÁCIÓ ÉS MENTÁLIS TÁMOGATÁS
  if (preg_match('/motiváció|feladom|nem.*megy|nehéz|lehetetlen|nincs.*erő|fárad/i', $message_clean)) {
    return "Értem, hogy nehéz időszak! 💪 De NE add fel!\n\n**A motiváció VÁLTOZÓ, a SZOKÁSOK ÁLLANDÓK!**\n\n**Tippek a kitartáshoz:**\n\n1. **Kis célok:**\n   - Ne 20 kg, hanem az első 2 kg\n   - Ne maraton, hanem 5 km\n   - 1 nap, 1 edzés egyszerre\n\n2. **Környezet:**\n   - Edzőruha elő este\n   - Táska bepakolva\n   - Edzőpartner\n\n3. **Rutinok:**\n   - Fix edzésidő (reggel 7, este 6)\n   - Automatizmus: ne gondolkodj, csak menj\n\n4. **Haladás láthatóvá:**\n   - Fotók (2 hetente)\n   - Mérések\n   - Edzésnapló\n   - App használat\n\n5. **Jutalom:**\n   - Heti 1 cheat meal\n   - Új edzőruha 5 kg után\n   - Masszázs havonta\n\n**Emlékezz:** \n\"Nem kell motiváltnak lenned. Csak elkezdeni.\nAz energia az edzés KÖZBEN jön.\"\n\n**Mi demotivált konkrétan? Beszéljük meg!**";
  }
  
  // 7. SÉRÜLÉSEK ÉS FÁJDALMAK
  if (preg_match('/fáj|fájdalom|sérül|sérülés|beteg|injury|hurt/i', $message_clean)) {
    return "⚠️ **FONTOS FIGYELMEZTETÉS!**\n\nFájdalom esetén:\n\n**1. AZONNAL állj meg az edzéssel!**\n\n**2. Különböztess meg:**\n\n✅ **\"Jó\" fájdalom:**\n- Izomláz (24-72h után)\n- Égő, fáradó érzés edzés közben\n- Pumpáltság\n\n❌ **\"Rossz\" fájdalom:**\n- Éles, szúró fájdalom\n- Ízületi fájdalom\n- Azonnal jelentkező fájdalom\n- Limitál a mozgásban\n- Másnap sem múlik\n\n**3. Ha ROSSZ fájdalom:**\n🏥 Menj orvoshoz!\n❌ NE gyógyítsd magad!\n❌ NE edzd túl!\n❌ NE keress neten!\n\n**4. Első segély (amíg orvoshoz jutsz):**\n- RICE módszer:\n  - Rest (pihenés)\n  - Ice (jég 20 perc)\n  - Compression (kompresszió)\n  - Elevation (megemelés)\n\n**Milyen jellegű a fájdalom? Hol és mikor jelentkezett?**\n\n(De hangsúlyozom: orvosi vizsgálat kell!)";
  }
  
  // 8. ALVÁS ÉS REGENERÁCIÓ
  if (preg_match('/alvás|alvás|aludni|fáradt|regenerál|pihen/i', $message_clean)) {
    return "Az alvás a titkos fegyver! 😴💪\n\n**Miért fontos:**\n- Izomnövekedés 80%-a alvás közben!\n- Hormonok termelése (növekedési, tesztoszteron)\n- Regeneráció\n- Teljesítmény helyreállítás\n\n**Optimális alvás:**\n📅 **Mennyiség:** 7-9 óra\n⏰ **Időzítés:** Fix elalvás (22:00-23:00)\n🌡️ **Hőmérséklet:** 18-20°C\n🌑 **Sötétség:** Teljes sötétség vagy szemtakaró\n📱 **Képernyő:** 1 órával alvás előtt kikapcsolni\n☕ **Koffein:** Ne 6 órán belül\n🥃 **Alkohol:** Kerülni\n\n**Javítási tippek:**\n\n1. **Rutinok:**\n   - Fix felébredés (hétvégén is!)\n   - Reggeli fény expozíció\n   - Este csökkentett fény\n\n2. **Kiegészítők (ha kell):**\n   - Magnézium 400mg\n   - ZMA komplex\n   - Levendula tea\n   - Glicinogén\n\n3. **Technikák:**\n   - 4-7-8 légzés\n   - Meditáció\n   - Olvasás (könyv, nem telefon!)\n\n**Hány órát szoktál aludni? Van alvási problémád?**";
  }
  
  // 9. KIEGÉSZÍTŐK
  if (preg_match('/kiegészítő|supplement|kreatin|bcaa|vitamin|omega/i', $message_clean)) {
    return "Kiegészítők - mi kell, mi nem? 💊\n\n**✅ HASZNOS (tudományosan bizonyított):**\n\n1. **Kreatin-monohidrát** ⭐⭐⭐⭐⭐\n   - Erőnövelés 10-15%\n   - Izomtömeg növelés\n   - Adag: 5g naponta\n   - Mikor: bármikor (nincs timing)\n   - Legolcsóbb és leghatékonyabb!\n\n2. **Protein por** ⭐⭐⭐\n   - Ha NEM éred el napi fehérjét\n   - Kényelmi faktor\n   - Whey: edzés után\n   - Kazein: este\n\n3. **Omega-3 (hal olaj)** ⭐⭐⭐⭐\n   - Gyulladáscsökkentő\n   - Szív egészség\n   - Adag: 2-3g EPA+DHA\n\n4. **D-vitamin** ⭐⭐⭐⭐\n   - Télen mindenképp!\n   - Immunrendszer, csontok\n   - Adag: 2000-4000 IU\n\n5. **Magnézium** ⭐⭐⭐\n   - Izomégetés ellen\n   - Alvás javítás\n   - Adag: 400mg\n\n6. **Koffein** ⭐⭐⭐⭐\n   - Teljesítmény +3-5%\n   - Fókusz\n   - Adag: 3-6mg/ttkg\n\n**❌ NEM KELL (pénzkidobás):**\n- BCAA (ha eszel fehérjét)\n- Zsírégető tabletta\n- Tesztoszteron booster\n- Detox termékek\n\n**Kérdezz bátran egy konkrét kiegészítőről!**";
  }
  
  // 10. ÁLTALÁNOS VÁLASZ - KONTEXTUÁLIS SEGÍTSÉG
  return "Érdekes kérdés! 🤔 Szeretnék segíteni, de pontosítsd légyszíves, mire vagy kíváncsi.\n\n**Népszerű témák:**\n\n💪 **Edzés:**\n- Edzéstervek (kezdő/haladó)\n- Gyakorlat technikák\n- Split vs. teljes test\n- Otthoni edzés\n\n🍎 **Táplálkozás:**\n- Kalória számítás\n- Makrók (fehérje, szénhidrát, zsír)\n- Étkezési időzítés\n- Kiegészítők\n\n🎯 **Célok:**\n- Fogyás stratégia\n- Izomépítés\n- Erőnövelés\n- Állóképesség\n\n💡 **Egyéb:**\n- Motiváció\n- Alvás és regeneráció\n- Sérülések megelőzése\n- Haladás követés\n\n**Írj egy konkrét kérdést, és részletesen válaszolok!** \n\nPéldául:\n- \"Hogyan kezdjem el a guggolást?\"\n- \"Mennyit kellene ennem fogyáshoz?\"\n- \"Milyen edzéstervet ajánlasz kezdőknek?\"";

// Újra lekérjük az üzeneteket a friss adatokhoz
$stmt = $pdo->prepare("SELECT * FROM ai_chat_messages WHERE user_id = ? ORDER BY created_at ASC LIMIT 100");
$stmt->execute([$user_id]);
$messages = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="hu">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>AI Edző Asszisztens - OnlineEdző</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;700;900&display=swap" rel="stylesheet">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    
    body {
      background: linear-gradient(135deg, #0a0e27, #0d1117, #1a1f3a);
      color: #e6edf3;
      font-family: 'Poppins', sans-serif;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }
    
    .particles {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      overflow: hidden;
      z-index: 0;
      pointer-events: none;
    }
    
    .particle {
      position: absolute;
      width: 3px;
      height: 3px;
      background: rgba(88, 166, 255, 0.3);
      border-radius: 50%;
      animation: float 25s infinite;
    }
    
    @keyframes float {
      0%, 100% { transform: translateY(0) translateX(0); opacity: 0; }
      10% { opacity: 1; }
      90% { opacity: 1; }
      100% { transform: translateY(-100vh) translateX(30px); opacity: 0; }
    }
    
    nav {
      background: rgba(10, 14, 39, 0.8);
      backdrop-filter: blur(20px);
      padding: 20px 50px;
      border-bottom: 1px solid rgba(88, 166, 255, 0.1);
      position: sticky;
      top: 0;
      z-index: 1000;
      box-shadow: 0 4px 30px rgba(0,0,0,0.3);
    }
    
    .logo {
      font-size: 1.8rem;
      font-weight: 900;
      background: linear-gradient(135deg, #58a6ff, #238636);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      letter-spacing: -1px;
    }
    
    nav a {
      color: #9bbcff;
      text-decoration: none;
      font-weight: 600;
      transition: 0.3s;
      padding: 8px 20px;
      border-radius: 8px;
    }
    
    nav a:hover {
      color: #58a6ff;
      background: rgba(88, 166, 255, 0.1);
    }
    
    .chat-container {
      max-width: 1000px;
      margin: 30px auto;
      padding: 0 20px 20px;
      position: relative;
      z-index: 1;
      flex: 1;
      display: flex;
      flex-direction: column;
    }
    
    h2 {
      text-align: center;
      margin-bottom: 15px;
      font-size: 3rem;
      font-weight: 900;
      background: linear-gradient(135deg, #58a6ff, #238636);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      animation: fadeInDown 0.8s ease;
    }
    
    .subtitle {
      text-align: center;
      color: #9ca3af;
      margin-bottom: 30px;
      font-size: 1.1rem;
    }
    
    @keyframes fadeInDown {
      from { opacity: 0; transform: translateY(-30px); }
      to { opacity: 1; transform: translateY(0); }
    }
    
    .chat-info {
      background: rgba(88, 166, 255, 0.1);
      border: 1px solid #58a6ff;
      border-radius: 15px;
      padding: 20px;
      margin-bottom: 25px;
      text-align: center;
    }
    
    .chat-info p {
      color: #9bbcff;
      margin: 0;
      line-height: 1.6;
    }
    
    .chat-box {
      background: rgba(10, 14, 39, 0.6);
      border: 1px solid rgba(88, 166, 255, 0.15);
      border-radius: 25px;
      padding: 30px;
      flex: 1;
      display: flex;
      flex-direction: column;
      box-shadow: 0 10px 40px rgba(0,0,0,0.3);
      backdrop-filter: blur(10px);
      min-height: 500px;
      max-height: calc(100vh - 400px);
    }
    
    .chat-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 25px;
      padding-bottom: 20px;
      border-bottom: 1px solid rgba(88, 166, 255, 0.2);
    }
    
    .chat-header h3 {
      color: #58a6ff;
      font-size: 1.5rem;
      font-weight: 700;
      margin: 0;
    }
    
    .btn-clear {
      background: rgba(239, 68, 68, 0.2);
      color: #ef4444;
      border: 1px solid #ef4444;
      padding: 8px 20px;
      border-radius: 10px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s;
      text-decoration: none;
      font-size: 0.9rem;
    }
    
    .btn-clear:hover {
      background: rgba(239, 68, 68, 0.3);
      transform: translateY(-2px);
      color: #ef4444;
    }
    
    .messages {
      flex: 1;
      overflow-y: auto;
      padding: 20px;
      background: rgba(0,0,0,0.2);
      border-radius: 15px;
      margin-bottom: 20px;
      scroll-behavior: smooth;
    }
    
    .messages::-webkit-scrollbar {
      width: 8px;
    }
    
    .messages::-webkit-scrollbar-track {
      background: rgba(0,0,0,0.2);
      border-radius: 10px;
    }
    
    .messages::-webkit-scrollbar-thumb {
      background: rgba(88, 166, 255, 0.3);
      border-radius: 10px;
    }
    
    .messages::-webkit-scrollbar-thumb:hover {
      background: rgba(88, 166, 255, 0.5);
    }
    
    .message {
      margin-bottom: 20px;
      animation: fadeIn 0.4s ease;
    }
    
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }
    
    .message.user {
      display: flex;
      justify-content: flex-end;
    }
    
    .message.assistant {
      display: flex;
      justify-content: flex-start;
    }
    
    .message-content {
      max-width: 75%;
      padding: 15px 20px;
      border-radius: 18px;
      position: relative;
      word-wrap: break-word;
      white-space: pre-wrap;
      line-height: 1.6;
    }
    
    .message.user .message-content {
      background: linear-gradient(135deg, #238636, #2ea043);
      color: white;
      border-bottom-right-radius: 4px;
    }
    
    .message.assistant .message-content {
      background: rgba(88, 166, 255, 0.15);
      color: #e6edf3;
      border: 1px solid rgba(88, 166, 255, 0.3);
      border-bottom-left-radius: 4px;
    }
    
    .message-avatar {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.5rem;
      margin: 0 12px;
    }
    
    .message.user .message-avatar {
      order: 2;
      background: linear-gradient(135deg, #238636, #2ea043);
    }
    
    .message.assistant .message-avatar {
      order: 1;
      background: linear-gradient(135deg, #58a6ff, #3b82f6);
    }
    
    .empty-state {
      text-align: center;
      padding: 80px 20px;
      color: #9ca3af;
    }
    
    .empty-state h4 {
      font-size: 1.8rem;
      margin-bottom: 15px;
      color: #58a6ff;
    }
    
    .empty-state p {
      margin-bottom: 10px;
      font-size: 1.05rem;
    }
    
    .suggested-questions {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 15px;
      margin-top: 30px;
    }
    
    .suggested-question {
      background: rgba(88, 166, 255, 0.1);
      border: 1px solid rgba(88, 166, 255, 0.3);
      padding: 15px;
      border-radius: 12px;
      cursor: pointer;
      transition: all 0.3s;
      text-align: center;
      font-size: 0.95rem;
    }
    
    .suggested-question:hover {
      background: rgba(88, 166, 255, 0.2);
      transform: translateY(-3px);
      box-shadow: 0 5px 20px rgba(88, 166, 255, 0.3);
    }
    
    .input-area {
      display: flex;
      gap: 15px;
      align-items: flex-end;
    }
    
    .input-wrapper {
      flex: 1;
      position: relative;
    }
    
    .form-control {
      background: rgba(255,255,255,0.08);
      border: 1px solid rgba(255,255,255,0.1);
      color: #fff;
      border-radius: 12px;
      padding: 14px 18px;
      font-size: 1rem;
      transition: all 0.3s;
      resize: none;
      min-height: 56px;
      max-height: 150px;
    }
    
    .form-control:focus {
      background: rgba(255,255,255,0.12);
      border-color: #58a6ff;
      color: #fff;
      box-shadow: 0 0 0 3px rgba(88, 166, 255, 0.15);
      outline: none;
    }
    
    .form-control::placeholder {
      color: #9ca3af;
    }
    
    .btn-send {
      background: linear-gradient(135deg, #238636, #2ea043);
      color: white;
      border: none;
      border-radius: 12px;
      padding: 14px 35px;
      font-weight: 700;
      font-size: 1.1rem;
      cursor: pointer;
      transition: all 0.3s;
      box-shadow: 0 6px 20px rgba(35, 134, 54, 0.4);
      white-space: nowrap;
    }
    
    .btn-send:hover:not(:disabled) {
      transform: translateY(-2px);
      box-shadow: 0 10px 30px rgba(35, 134, 54, 0.6);
    }
    
    .btn-send:disabled {
      opacity: 0.5;
      cursor: not-allowed;
    }
    
    @media (max-width: 768px) {
      nav {
        padding: 15px 20px;
      }
      
      .logo {
        font-size: 1.4rem;
      }
      
      nav a {
        padding: 6px 12px;
        font-size: 0.9rem;
      }
      
      h2 {
        font-size: 2.2rem;
      }
      
      .chat-box {
        padding: 20px 15px;
        max-height: calc(100vh - 350px);
      }
      
      .message-content {
        max-width: 85%;
        padding: 12px 16px;
        font-size: 0.95rem;
      }
      
      .input-area {
        flex-direction: column;
      }
      
      .btn-send {
        width: 100%;
      }
      
      .suggested-questions {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>
<body>

  <div class="particles">
    <div class="particle" style="left: 10%; animation-delay: 0s;"></div>
    <div class="particle" style="left: 25%; animation-delay: 3s;"></div>
    <div class="particle" style="left: 40%; animation-delay: 6s;"></div>
    <div class="particle" style="left: 55%; animation-delay: 9s;"></div>
    <div class="particle" style="left: 70%; animation-delay: 12s;"></div>
    <div class="particle" style="left: 85%; animation-delay: 15s;"></div>
  </div>

  <nav class="d-flex justify-content-between align-items-center">
    <a href="index.php" style="text-decoration: none;">
      <div class="logo">💪 OnlineEdző</div>
    </a>
    <div>
      <a href="index.php">🏠 Főoldal</a>
      <a href="logout.php">Kijelentkezés</a>
    </div>
  </nav>

  <div class="chat-container">
    <h2>🤖 AI Edző Asszisztens</h2>
    <p class="subtitle">Kérdezz bármit edzésről, táplálkozásról és egészségről!</p>

    <div class="chat-info">
      <p>💡 <strong>Tipp:</strong> Minél részletesebb a kérdésed, annál pontosabb választ tudok adni! Kérdezz rám edzéstervekről, gyakorlat technikáról, táplálkozásról vagy motivációról!</p>
    </div>

    <div class="chat-box">
      <div class="chat-header">
        <h3>💬 Chat</h3>
        <?php if (!empty($messages)): ?>
          <a href="?clear_chat=1" class="btn-clear" onclick="return confirm('Biztosan törlöd a beszélgetés előzményeket?')">
            🗑️ Előzmények törlése
          </a>
        <?php endif; ?>
      </div>

      <div class="messages" id="messages">
        <?php if (empty($messages)): ?>
          <div class="empty-state">
            <h4>👋 Üdv! Én vagyok az AI Edződ!</h4>
            <p>Kérdezz bátran bármit, amiben segíthetek:</p>
            
            <div class="suggested-questions">
              <div class="suggested-question" onclick="askQuestion('Hogyan kezdjem el az edzést?')">
                🏃 Hogyan kezdjem el?
              </div>
              <div class="suggested-question" onclick="askQuestion('Milyen edzéstervet ajánlasz izomépítéshez?')">
                💪 Izomépítő terv
              </div>
              <div class="suggested-question" onclick="askQuestion('Mit együnk fogyáshoz?')">
                🍎 Fogyókúra táplálkozás
              </div>
              <div class="suggested-question" onclick="askQuestion('Hogyan javítsam a guggolás technikám?')">
                🏋️ Gyakorlat technika
              </div>
              <div class="suggested-question" onclick="askQuestion('Mennyit kell innom naponta?')">
                💧 Folyadékbevitel
              </div>
              <div class="suggested-question" onclick="askQuestion('Mikor egyem a fehérjét?')">
                🥤 Fehérje timing
              </div>
            </div>
          </div>
        <?php else: ?>
          <?php foreach ($messages as $msg): ?>
            <div class="message <?= htmlspecialchars($msg['role']) ?>">
              <?php if ($msg['role'] === 'assistant'): ?>
                <div class="message-avatar">🤖</div>
              <?php endif; ?>
              
              <div class="message-content">
                <?= nl2br(htmlspecialchars($msg['message'])) ?>
              </div>
              
              <?php if ($msg['role'] === 'user'): ?>
                <div class="message-avatar">👤</div>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

      <form method="POST" class="input-area" onsubmit="return validateMessage()">
        <div class="input-wrapper">
          <textarea 
            name="user_message" 
            id="messageInput"
            class="form-control" 
            placeholder="Írd be a kérdésedet..."
            rows="1"
            required
            onkeydown="handleKeyPress(event)"
            oninput="autoResize(this)"></textarea>
        </div>
        <button type="submit" class="btn-send" id="sendBtn">
          🚀 Küldés
        </button>
      </form>
    </div>
  </div>

  <script>
    // Auto-scroll to bottom on page load
    window.addEventListener('load', function() {
      const messages = document.getElementById('messages');
      messages.scrollTop = messages.scrollHeight;
    });

    // Auto-resize textarea
    function autoResize(textarea) {
      textarea.style.height = 'auto';
      textarea.style.height = Math.min(textarea.scrollHeight, 150) + 'px';
    }

    // Handle Enter key (Shift+Enter for new line, Enter to send)
    function handleKeyPress(event) {
      if (event.key === 'Enter' && !event.shiftKey) {
        event.preventDefault();
        document.querySelector('.input-area').dispatchEvent(new Event('submit', {bubbles: true, cancelable: true}));
      }
    }

    // Validate message before submit
    function validateMessage() {
      const input = document.getElementById('messageInput');
      const btn = document.getElementById('sendBtn');
      
      if (input.value.trim() === '') {
        return false;
      }
      
      btn.disabled = true;
      btn.textContent = '⏳ Küldés...';
      return true;
    }

    // Suggested question click handler
    function askQuestion(question) {
      document.getElementById('messageInput').value = question;
      document.getElementById('messageInput').focus();
    }

    // Focus on input on page load
    document.getElementById('messageInput').focus();
  </script>

</body>
</html>
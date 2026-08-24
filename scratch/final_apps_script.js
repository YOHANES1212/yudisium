// ========================================================
// CODE GOOGLE APPS SCRIPT LENGKAP (FINAL VERSION)
// Salin SELURUH isi file ini ke Google Sheets:
// Ekstensi -> Apps Script -> ganti semua isi file dengan kode ini -> Simpan (Ctrl+S) -> Deploy Ulang Web App.
// ========================================================

// ========================================================
// BAGIAN 1: SISTEM VALIDASI & KIRIM EMAIL (DESAIN PROPER)
// ========================================================
function prosesValidasiManual(e) {
  var ss = SpreadsheetApp.getActiveSpreadsheet();
  var sheet = (e && e.range) ? e.range.getSheet() : ss.getSheets()[0];

  // Konfigurasi Kolom
  var colEmail = 2;          // Kolom B
  var colNama = 4;           // Kolom D
  var colNIM = 5;            // Kolom E
  var colProdi = 6;          // Kolom F
  var colStatusValidasi = 14; // Kolom N
  var colIDUnik = 15;        // Kolom O
  var colStatusEmail = 16;   // Kolom P

  // Skenario A: Jika diketik manual oleh manusia di Google Sheet
  if (e && e.range) {
    var row = e.range.getRow();
    var col = e.range.getColumn();
    if (row === 1) return;

    var nilaiCell = sheet.getRange(row, col).getValue().toString().trim().toLowerCase();

    if (col === colStatusValidasi && nilaiCell === "valid") {
      prosesSatuPeserta(sheet, row, colEmail, colNama, colNIM, colProdi, colIDUnik, colStatusEmail);
    }
  } 
  // Skenario B: Jika dipicu oleh API / Trigger onChange / Tombol Run
  else {
    var data = sheet.getDataRange().getValues();
    for (var r = 1; r < data.length; r++) {
      var statusVal = String(data[r][colStatusValidasi - 1] || '').trim().toLowerCase();
      var statusEm  = String(data[r][colStatusEmail - 1] || '').trim();
      
      if (statusVal === "valid" && statusEm !== "Terkirim ✅") {
        prosesSatuPeserta(sheet, r + 1, colEmail, colNama, colNIM, colProdi, colIDUnik, colStatusEmail);
      }
    }
  }
}

// Fungsi bantu eksekusi verifikasi per peserta
function prosesSatuPeserta(sheet, row, colEmail, colNama, colNIM, colProdi, colIDUnik, colStatusEmail) {
  var statusEmailSekarang = sheet.getRange(row, colStatusEmail).getValue();
  if (statusEmailSekarang === "Terkirim ✅") return; 

  try {
    var emailTujuan = sheet.getRange(row, colEmail).getValue().toString().trim();
    var namaPeserta = sheet.getRange(row, colNama).getValue();
    var nimPeserta = sheet.getRange(row, colNIM).getValue();
    var prodiPeserta = sheet.getRange(row, colProdi).getValue();

    if (!emailTujuan || emailTujuan === "-") {
      sheet.getRange(row, colStatusEmail).setValue("Gagal: Email kosong");
      return;
    }

    var idUnik = "YDS-" + row + "-" + new Date().getTime().toString().slice(-4);
    sheet.getRange(row, colIDUnik).setValue(idUnik);

    // Link Web App Scanner
    var urlWebAppGua = "https://script.google.com/macros/s/AKfycbzcqahW0zz10p1VqClGORtZdZxO37toNF-VSG0Z4A3DYJ6IuZnqxtBJ1iKYV5ISZzEY/exec"; 
    var isiQrCode = urlWebAppGua + "?id=" + idUnik;
    var qrCodeUrl = "https://quickchart.io/qr?size=300&text=" + encodeURIComponent(isiQrCode);

    // Eksekusi kirim email
    kirimEmailQR(emailTujuan, namaPeserta, nimPeserta, prodiPeserta, qrCodeUrl);
    sheet.getRange(row, colStatusEmail).setValue("Terkirim ✅");
    
  } catch (error) {
    sheet.getRange(row, colStatusEmail).setValue("Gagal: " + error.message);
  }
}

// Fungsi Desain Email Keren
function kirimEmailQR(emailTujuan, namaPeserta, nimPeserta, prodiPeserta, qrUrl) {
  var subject = "Tiket Yudisium - " + (namaPeserta || "Peserta");
  
  var htmlBody = `
    <div style="max-width: 550px; margin: 0 auto; border: 1px solid #e0e0e0; border-radius: 10px; padding: 30px; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #333;">
      <h2 style="text-align: center; color: #202124; margin-bottom: 5px; font-size: 22px;">Tiket Presensi Yudisium</h2>
      <p style="text-align: center; color: #188038; font-weight: bold; font-size: 15px; margin-top: 0; border-bottom: 1px solid #f0f0f0; padding-bottom: 20px;">Pembayaran Terverifikasi (Lunas)</p>
      
      <p style="font-size: 15px; margin-top: 20px; color: #333;">Halo <b>${namaPeserta || 'Peserta'}</b>,</p>
      <p style="font-size: 15px; line-height: 1.6; color: #333;">Pembayaran yudisium Anda telah diverifikasi. Tunjukkan QR Code berikut kepada panitia:</p>
      
      <div style="text-align: center; margin: 30px 0;">
        <img src="${qrUrl}" alt="QR Code" width="200" height="200" style="border: 1px solid #dadce0; border-radius: 8px; padding: 10px;">
      </div>
      
      <table style="width: 100%; font-size: 14px; margin-bottom: 25px; color: #5f6368;">
        <tr>
          <td style="padding: 8px 0;">Nama:</td>
          <td style="padding: 8px 0; text-align: right; font-weight: bold; color: #202124;">${namaPeserta || '-'}</td>
        </tr>
        <tr>
          <td style="padding: 8px 0;">NIM:</td>
          <td style="padding: 8px 0; text-align: right; font-weight: bold; color: #202124;">${nimPeserta || '-'}</td>
        </tr>
        <tr>
          <td style="padding: 8px 0;">Program Studi:</td>
          <td style="padding: 8px 0; text-align: right; font-weight: bold; color: #202124;">${prodiPeserta || '-'}</td>
        </tr>
      </table>
      
     <div style="background-color: #fcf1f1; border: 1px solid #f8d7da; border-radius: 6px; padding: 15px; text-align: center; font-size: 13px; color: #721c24; line-height: 1.5;">
        <b>⚠️ PENTING</b><br>
        QR Code ini digunakan sebagai tiket masuk pada saat acara dan <b>hanya berlaku untuk 1 (satu) kali scan</b>.<br>
        <i style="color: #6c757d; display: inline-block; margin-top: 8px;">Silakan screenshot email ini untuk mempercepat antrean registrasi.</i>
      </div>
    </div>
  `;

  MailApp.sendEmail({ 
    to: emailTujuan, 
    subject: subject, 
    htmlBody: htmlBody,
    name: "Panitia Yudisium" 
  });
}

// ========================================================
// BAGIAN 2: SISTEM SCANNER + API LARAVEL (doGet)
// ========================================================
function doGet(e) {
  // API untuk aplikasi Laravel
  if (e && e.parameter && e.parameter.action === 'peserta') {
    return getAllPesertaJSON();
  }

  var idUnik = e && e.parameter ? e.parameter.id : null;
  
  if (!idUnik) {
    return HtmlService.createHtmlOutput("<h2>⚠️ Buka dari hasil scan QR Code ya!</h2>").addMetaTag('viewport', 'width=device-width, initial-scale=1');
  }
  
  var sheet = SpreadsheetApp.getActiveSpreadsheet().getSheets()[0];
  var data = sheet.getDataRange().getValues();
  
  var colNama = 4;        // Kolom D
  var colNIM = 5;         // Kolom E
  var colProdi = 6;       // Kolom F
  var colIDUnik = 15;     // Kolom O
  var colKehadiran = 17;  // Kolom Q
  
  var htmlOutput = "";
  var ketemu = false;
  
  for (var i = 1; i < data.length; i++) {
    if (data[i][colIDUnik - 1] === idUnik) {
      ketemu = true;
      var nama = data[i][colNama - 1];
      var nim = data[i][colNIM - 1];
      var prodi = data[i][colProdi - 1];
      
      if (data[i][colKehadiran - 1] !== "") {
         var waktuLama = Utilities.formatDate(new Date(data[i][colKehadiran - 1]), "GMT+7", "HH:mm:ss");
         htmlOutput = "<div style='background:#f8d7da; color:#721c24; padding:20px; border-radius:10px; border:1px solid #f5c6cb;'>" +
                      "<h2>❌ SUDAH DIGUNAKAN</h2>" +
                      "<p>Peserta ini sudah absen pada pukul <b>" + waktuLama + " WIB</b>.</p>" +
                      "<p style='text-align:left;'><b>Nama:</b> " + nama + "<br><b>NIM:</b> " + nim + "</p></div>";
      } else {
         var timestamp = new Date();
         sheet.getRange(i + 1, colKehadiran).setValue(timestamp); 
         var waktuBaru = Utilities.formatDate(timestamp, "GMT+7", "HH:mm:ss");
         
         htmlOutput = "<div style='background:#d4edda; color:#155724; padding:20px; border-radius:10px; border:1px solid #c3e6cb;'>" +
                      "<h2>✅ KEHADIRAN VALID</h2>" +
                      "<div style='text-align:left; font-size:16px; line-height:1.6;'>" +
                      "<b>Nama:</b> " + nama + "<br>" +
                      "<b>NIM:</b> " + nim + "<br>" +
                      "<b>Prodi:</b> " + prodi + "<br>" +
                      "<b>Waktu Kehadiran:</b> " + waktuBaru + " WIB</div></div>";
      }
      break;
    }
  }
  
  if (!ketemu) {
     htmlOutput = "<div style='background:#fff3cd; color:#856404; padding:20px; border-radius:10px;'><h2>⚠️ QR TIDAK DIKENAL</h2><p>Data tidak ditemukan di database.</p></div>";
  }
  
  var style = "<style>body{font-family:'Segoe UI', sans-serif; padding:15px; text-align:center; background:#f4f7f6;}</style>";
  return HtmlService.createHtmlOutput(style + htmlOutput).addMetaTag('viewport', 'width=device-width, initial-scale=1');
}

// ========================================================
// BAGIAN 3: API UNTUK APLIKASI LARAVEL (JSON & POST UPDATE)
// ========================================================

function getAllPesertaJSON() {
  var sheet = SpreadsheetApp.getActiveSpreadsheet().getSheets()[0];
  var data = sheet.getDataRange().getValues();
  var headers = data[0];
  var result = [];

  for (var i = 1; i < data.length; i++) {
    var obj = {};
    for (var j = 0; j < headers.length; j++) {
      var val = data[i][j];
      if (val instanceof Date) {
        val = Utilities.formatDate(val, "GMT+7", "yyyy-MM-dd HH:mm:ss");
      }
      obj[headers[j]] = val;
    }
    result.push(obj);
  }

  return ContentService.createTextOutput(JSON.stringify(result))
    .setMimeType(ContentService.MimeType.JSON);
}

function doPost(e) {
  try {
    var payload = {};
    if (e && e.postData && e.postData.contents) {
      try {
        payload = JSON.parse(e.postData.contents);
      } catch (err) {
        payload = e.parameter || {};
      }
    } else if (e && e.parameter) {
      payload = e.parameter;
    }

    var reqNim   = String(payload.nim || payload.NIM || '').trim();
    var reqEmail = String(payload.email || payload.Email || payload['Email Address'] || payload['Email Address '] || '').trim().toLowerCase();
    var reqNama  = String(payload.nama || payload.Nama || payload['Nama Lengkap'] || payload['Nama Lengkap '] || '').trim().toLowerCase();
    var updates  = payload.updates || {};

    // Standardize status & nomor_kursi if sent flat
    var statusValue = String(payload.status || payload.Status || payload['Status Pembayaran'] || updates['Status Pembayaran'] || '').trim();
    var kursiValue  = String(payload.nomor_kursi || payload['Nomor Kursi'] || updates['Nomor Kursi'] || '').trim();

    if (statusValue) {
      updates['Status Pembayaran'] = statusValue;
    }
    if (kursiValue) {
      updates['Nomor Kursi'] = kursiValue;
      updates['Plotting Kursi'] = kursiValue;
    }

    var sheet = SpreadsheetApp.getActiveSpreadsheet().getSheets()[0];
    var data = sheet.getDataRange().getValues();
    var headers = data[0];

    // Identify Column Indexes (1-indexed)
    var colEmailIndex = 2; // Kolom B
    var colNamaIndex  = 4; // Kolom D
    var colNIMIndex   = 5; // Kolom E
    var colProdiIndex = 6; // Kolom F
    var colStatusIndex = 14; // Kolom N (Status Pembayaran)
    var colIDUnikIndex = 15; // Kolom O (ID Unik)
    var colStatusEmailIndex = 16; // Kolom P (Status Email)

    // Search for header columns dynamically if available
    for (var h = 0; h < headers.length; h++) {
      var hName = String(headers[h]).trim();
      if (hName === 'NIM') colNIMIndex = h + 1;
      if (hName === 'Email Address' || hName === 'Email') colEmailIndex = h + 1;
      if (hName === 'Nama Lengkap' || hName === 'Nama') colNamaIndex = h + 1;
      if (hName === 'Status Pembayaran') colStatusIndex = h + 1;
      if (hName === 'ID Unik') colIDUnikIndex = h + 1;
      if (hName === 'Status Email') colStatusEmailIndex = h + 1;
    }

    var matchedRow = -1;

    for (var i = 1; i < data.length; i++) {
      var cellNim   = String(data[i][colNIMIndex - 1] || '').trim();
      var cellEmail = String(data[i][colEmailIndex - 1] || '').trim().toLowerCase();
      var cellNama  = String(data[i][colNamaIndex - 1] || '').trim().toLowerCase();

      // Match by NIM, Email, or Name
      if (reqNim !== '' && reqNim !== '-' && cellNim === reqNim) {
        matchedRow = i + 1;
        break;
      }
      if (reqEmail !== '' && reqEmail !== '-' && cellEmail === reqEmail) {
        matchedRow = i + 1;
        break;
      }
      if (reqNama !== '' && reqNama !== '-' && cellNama === reqNama) {
        matchedRow = i + 1;
        break;
      }
    }

    if (matchedRow > 0) {
      // 1. Update matching fields in Google Sheet
      for (var key in updates) {
        var colIndex = -1;
        for (var c = 0; c < headers.length; c++) {
          if (String(headers[c]).trim() === String(key).trim()) {
            colIndex = c + 1;
            break;
          }
        }
        if (colIndex > 0) {
          sheet.getRange(matchedRow, colIndex).setValue(updates[key]);
        }
      }

      // 2. If status is valid, trigger QR Code generation and email dispatch automatically
      var finalStatus = String(updates['Status Pembayaran'] || statusValue || '').trim().toLowerCase();
      if (finalStatus === 'valid' || finalStatus === 'validkan') {
        sheet.getRange(matchedRow, colStatusIndex).setValue('valid');
        prosesSatuPeserta(sheet, matchedRow, colEmailIndex, colNamaIndex, colNIMIndex, colProdiIndex, colIDUnikIndex, colStatusEmailIndex);
      }

      return ContentService.createTextOutput(JSON.stringify({ success: true, row: matchedRow }))
        .setMimeType(ContentService.MimeType.JSON);
    }

    return ContentService.createTextOutput(JSON.stringify({ success: false, error: 'Peserta tidak ditemukan' }))
      .setMimeType(ContentService.MimeType.JSON);

  } catch (err) {
    return ContentService.createTextOutput(JSON.stringify({ success: false, error: err.message }))
      .setMimeType(ContentService.MimeType.JSON);
  }
}

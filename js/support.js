let extensionTable = [
  ["CR2", "Canon RAW Image (CR2)"],
  ["CR3", "Canon RAW Image (CR3)"],
  ["CRW", "Canon Old RAW Image (CRW)"],
  ["JPG", "JPEG Image"],
  ["JPEG", "JPEG Image"],
  ["PNG", "Portable Network Graphic"],
  ["GIF", "Compugraphic Image File (GIF)"],
  ["NEF", "Nikon RAW Image (NEF)"],
  ["NRW", "Nikon RAW Image (NRW)"],
  ["RAF", "Fuji RAW Image"],
  ["TIF", "Tagged Image File"],
  ["TIFF", "Tagged Image File"],
  ["BMP", "Windows Bitmap"],
  ["PEF", "Pentax RAW Image"],
  ["ORF", "Olympus RAW Image"],
  ["ARW", "Sony RAW Image"],
  ["SR2", "Sony RAW Image"],
  ["SRF", "Sony RAW Image"],
  ["X3F", "Sigma RAW Image"],
  ["SRW", "Samsung RAW Image"],
  ["MEF", "Mamiya RAW Image"],
  ["MRW", "Minolta RAW Image"],
  ["ERF", "Epson RAW Image"],
  ["RWL", "Leica RAW Image"],
  ["RW2", "Panasonic RAW Image"],
  ["KDC", "Kodak RAW Image"],
  ["DCR", "Kodak RAW Image"],
  ["3FR", "Hasselblad RAW Image"],
  ["FFF", "Hasselblad RAW Image"],
  ["RAW", "Generic RAW Image"],
  ["MOS", "Leaf Aptus"],
];

function deleteFile(fileToDelete) {
  let dataToPost = {};
  dataToPost.fileToDelete = fileToDelete;
  $.ajax({
    url: "../php/deleteFile.php",
    type: "GET",
    data: dataToPost,
    success: function () {},
  });
}

function getFileSizeSuffix() {
  switch (localStorage.getItem("byteSize")) {
    case "0":
      return "bytes";
    case "1":
      return "Kb";
    case "2":
      return "Mb";
    case "3":
      return "Gb";
  }
}

// default settings
localStorage.setItem("byteSize", 2); // power of 1000, ie. 1000^2 = Mb, 1000^1 = Kilobytes, 1000^0 = bytes
localStorage.setItem("GPS_Coords", 0); // 1 = Sexagesimal GPS Coordinates, 0 = Decimal Coordinates
localStorage.setItem("Altitude_Distance_Unit", 0); // 0 = metres, 1 = feet

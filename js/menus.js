const showUploadButton = document.querySelector("#showUploader");
// const showThumbnail = document.querySelector("#showThumbnail");
const showImage = document.querySelector("#showImage");
// const showBasicEXIF = document.querySelector("#showBasicTags");
const showFullEXIF = document.querySelector("#showAllTags");
const imageView = document.querySelector(".imageView");
// const thumbView = document.querySelector(".thumbnailView");
const basicEXIFView = document.querySelector(".basicEXIFView");
const fullEXIFView = document.querySelector(".fullEXIFView");
const editTags = document.querySelector("#editTags");
const R3Grid = document.querySelector("#R3Grid");
const diagonals = document.querySelector("#Diagonals");
const AFPoints = document.querySelector("#showAFPoints");
const SelectedAFPoints = document.querySelector("#showSelectedAFPoints");
const Highlights = document.querySelector("#showHighlights");
const Shadows = document.querySelector("#showShadows");
const phiGrid = document.querySelector("#PhiGrid");
const showMap = document.querySelector("#showMap");
const about = document.querySelector("#aboutDrop");
const geoTag = document.querySelector("#geoTagImage");
const altUnit = document.querySelector(".toggleAltitudeUnit");

let AFCanvas;
let HighCanvas;
let viewRotation = 0;
let whites;
let blacks;
let reds;
let greens;
let blues;
let lumins;
let latitudeG;
let longitudeG;
let editableTags = {
  make: "",
  model: "",
  lens: "",
  description: "",
  artist: "",
  copyright: "",
  rating: "",
  ratingPercent: "",
  comment: "",
  shutter: "",
  fnumber: "",
  ISO: "",
};

const showUploader = function (e) {
  document.querySelector(".uploaderCanvas").classList.toggle("isHidden");
  if (showUploadButton.textContent == "Show Uploader") {
    showUploadButton.textContent = "Hide Uploader";
  } else {
    showUploadButton.textContent = "Show Uploader";
  }
};

const toggleImage = function (e) {
  this.classList.toggle("dropdown-item-checked");
  imageView.classList.toggle("isHidden");
};
// const toggleThumbnail = function (e) {
//   this.classList.toggle("dropdown-item-checked");
//   thumbView.classList.toggle("isHidden");
// };
// const toggleBasicEXIF = function (e) {
//   this.classList.toggle("dropdown-item-checked");
//   basicEXIFView.classList.toggle("isHidden");
//   if (this.classList.contains("dropdown-item-checked")) {
//     fullEXIFView.classList.add("isHidden");
//     showAllTags.classList.remove("dropdown-item-checked");
//   }
// };
const toggleFullEXIF = function (e) {
  // fullEXIFView.classList.toggle("isHidden");

  this.classList.toggle("dropdown-item-checked");
  if (this.classList.contains("dropdown-item-checked")) {
    basicEXIFView.classList.add("isHidden");
    fullEXIFView.classList.remove("isHidden");
    dataToPost = {};
    dataToPost.filename = "../" + thisImage.filename;
    dataToPost.endian = thisImage.endian;
    dataToPost.jpegOffset = thisImage.imageType;
    $.ajax({
      method: "POST",
      async: true,
      url: "../php/EXIF/fullEXIF.php",
      data: dataToPost,
      success: (data) => {
        data = JSON.parse(data);
        data.sort(function (a, b) {
          return a.Group - b.Group;
        });

        let fullTable = `<h3 class='sectionTitle'>Full Metadata</h3><table class='table table-sm'>`;
        let IFD0 = "",
          IFD1 = "",
          IFD2 = "",
          IFD3 = "",
          EXIF = "",
          GPS = "",
          Makernote = "",
          mnCameraSettings = "",
          mnShotInfo = "",
          mnCameraInfo = "",
          mnTimeInfo = "",
          mnSensorInfo = "",
          mnProcessingInfo = "",
          mnCropInfo = "",
          mnFileInfo = "",
          mnCFuncExposure = "",
          mnCFuncImageFD = "",
          mnCFuncAFDrive = "",
          mnCFuncOps = "",
          mnAspectInfo = "",
          mnMultiExp = "",
          latitude = "",
          latitudeMultiplier = 1,
          longitude = "",
          longitudeMultiplier = 1;

        data.forEach(function (cur) {
          if (cur.Group == 0) {
            IFD0 += `<tr><td width='40%'>${cur.Tag}</td><td>${cur.Data}</td></tr>`;
          }
          if (cur.Group == 1) {
            IFD1 += `<tr><td>${cur.Tag}</td><td>${cur.Data}</td></tr>`;
          }
          if (cur.Group == 2) {
            IFD2 += `<tr><td>${cur.Tag}</td><td>${cur.Data}</td></tr>`;
          }
          if (cur.Group == 3) {
            IFD3 += `<tr><td>${cur.Tag}</td><td>${cur.Data}</td></tr>`;
          }
          if (cur.Group == 4) {
            EXIF += `<tr><td>${cur.Tag}</td><td>${cur.Data}</td></tr>`;
          }
          if (cur.Group == 5) {
            if (cur.Data == "South") {
              latitudeMultiplier = -1;
            }
            if (cur.Data == "West") {
              longitudeMultiplier = -1;
            }

            if (cur.Tag == "Latitude") {
              latitude = cur.Data;
              const st = latitude.indexOf("(");
              latitude =
                parseFloat(latitude.substring(0, st - 2)) * latitudeMultiplier;
            }
            if (cur.Tag == "Longitude") {
              longitude = cur.Data;
              const st = longitude.indexOf("(");
              longitude =
                parseFloat(longitude.substring(0, st - 2)) *
                longitudeMultiplier;
            }
            GPS += `<tr><td>${cur.Tag}</td><td>${cur.Data}</td></tr>`;
          }
          if (cur.Group == 6) {
            Makernote += `<tr><td>${cur.Tag}</td><td>${cur.Data}</td></tr>`;
          }
          if (cur.Group == 7) {
            mnCameraSettings += `<tr><td>${cur.Tag}</td><td>${cur.Data}</td></tr>`;
          }
          if (cur.Group == 8) {
            mnShotInfo += `<tr><td>${cur.Tag}</td><td>${cur.Data}</td></tr>`;
          }
          if (cur.Group == 9) {
            mnCameraInfo += `<tr><td>${cur.Tag}</td><td>${cur.Data}</td></tr>`;
          }
          if (cur.Group == 10) {
            mnTimeInfo += `<tr><td>${cur.Tag}</td><td>${cur.Data}</td></tr>`;
          }
          if (cur.Group == 11) {
            mnSensorInfo += `<tr><td>${cur.Tag}</td><td>${cur.Data}</td></tr>`;
          }
          if (cur.Group == 12) {
            mnProcessingInfo += `<tr><td>${cur.Tag}</td><td>${cur.Data}</td></tr>`;
          }
          if (cur.Group == 13) {
            mnCropInfo += `<tr><td>${cur.Tag}</td><td>${cur.Data}</td></tr>`;
          }
          if (cur.Group == 14) {
            mnFileInfo += `<tr><td>${cur.Tag}</td><td>${cur.Data}</td></tr>`;
          }
          if (cur.Group == 15) {
            mnCFuncExposure += `<tr><td>${cur.Tag}</td><td>${cur.Data}</td></tr>`;
          }
          if (cur.Group == 16) {
            mnCFuncImageFD += `<tr><td>${cur.Tag}</td><td>${cur.Data}</td></tr>`;
          }
          if (cur.Group == 17) {
            mnCFuncAFDrive += `<tr><td>${cur.Tag}</td><td>${cur.Data}</td></tr>`;
          }
          if (cur.Group == 18) {
            mnCFuncOps += `<tr><td>${cur.Tag}</td><td>${cur.Data}</td></tr>`;
          }
          if (cur.Group == 19) {
            mnAspectInfo += `<tr><td>${cur.Tag}</td><td>${cur.Data}</td></tr>`;
          }
          if (cur.Group == 20) {
            mnMultiExp += `<tr><td>${cur.Tag}</td><td>${cur.Data}</td></tr>`;
          }
        });

        if (IFD0) {
          fullTable +=
            "<tr class='table-info'><th colspan='2'>IFD 0 (EMBEDDED JPEG)</th></tr>" +
            IFD0;
        }
        if (IFD1) {
          fullTable +=
            "<tr class='table-info'><th colspan='2'>IFD 1 (THUMBNAIL)</th></tr>" +
            IFD1;
        }
        if (IFD2) {
          fullTable +=
            "<tr class='table-info'><th colspan='2'>IFD 2 (LCD PREVIEW)</th></tr>" +
            IFD2;
        }
        if (IFD3) {
          fullTable +=
            "<tr class='table-info'><th colspan='2'>IFD 3 (RAW IMAGE)</th></tr>" +
            IFD3;
        }
        if (EXIF) {
          fullTable +=
            "<tr class='table-info'><th colspan='2'>EXIF Data</th></tr>" + EXIF;
        }
        if (GPS) {
          GPS += `<tr><td></td><td><div class='btn btn-success btn-sm' onclick='viewOnMap("${localStorage.getItem(
            "name"
          )}", ${latitude},${longitude})'>view on map</div></td><tr>`;
          fullTable +=
            "<tr class='table-info'><th colspan='2'>GPS Info</th></tr>" + GPS;
        }

        if (Makernote) {
          fullTable +=
            "<tr class='table-info'><th colspan='2'>Canon Proprietary Data</th></tr>" +
            Makernote;
        }
        if (mnCameraSettings) {
          fullTable +=
            "<tr class='table-info'><th colspan='2'>Camera Settings</th></tr>" +
            mnCameraSettings;
        }
        if (mnShotInfo) {
          fullTable +=
            "<tr class='table-info'><th colspan='2'>Shot Info</th></tr>" +
            mnShotInfo;
        }
        if (mnCameraInfo) {
          fullTable +=
            "<tr class='table-info'><th colspan='2'>Camera Info</th></tr>" +
            mnCameraInfo;
        }
        if (mnFileInfo) {
          fullTable +=
            "<tr class='table-info'><th colspan='2'>File Info</th></tr>" +
            mnFileInfo;
        }
        if (mnTimeInfo) {
          fullTable +=
            "<tr class='table-info'><th colspan='2'>Time Zone Info</th></tr>" +
            mnTimeInfo;
        }
        if (mnSensorInfo) {
          fullTable +=
            "<tr class='table-info'><th colspan='2'>Sensor Info</th></tr>" +
            mnSensorInfo;
        }
        if (mnProcessingInfo) {
          fullTable +=
            "<tr class='table-info'><th colspan='2'>Processing Info</th></tr>" +
            mnProcessingInfo;
        }
        if (mnCFuncExposure) {
          fullTable +=
            "<tr class='table-info'><th colspan='2'>Custom Functions - Exposure</th></tr>" +
            mnCFuncExposure;
        }
        if (mnCFuncImageFD) {
          fullTable +=
            "<tr class='table-info'><th colspan='2'>Custom Functions - Image, Flash & Display</th></tr>" +
            mnCFuncImageFD;
        }
        if (mnCFuncAFDrive) {
          fullTable +=
            "<tr class='table-info'><th colspan='2'>Custom Functions - Auto Focus and Drive</th></tr>" +
            mnCFuncAFDrive;
        }
        if (mnCFuncOps) {
          fullTable +=
            "<tr class='table-info'><th colspan='2'>Custom Functions - Operations</th></tr>" +
            mnCFuncOps;
        }
        if (mnMultiExp) {
          fullTable +=
            "<tr class='table-info'><th colspan='2'>Multiple Exposure</th></tr>" +
            mnMultiExp;
        }
        if (mnCropInfo) {
          fullTable +=
            "<tr class='table-info'><th colspan='2'>Crop Info</th></tr>" +
            mnCropInfo;
        }
        if (mnAspectInfo) {
          fullTable +=
            "<tr class='table-info'><th colspan='2'>Aspect Info</th></tr>" +
            mnAspectInfo;
        }
        fullTable += "</table>";
        document.querySelector(
          ".fullEXIFView"
        ).innerHTML = `<div class="container">${fullTable}</div>`;
      },
    });
  } else {
    basicEXIFView.classList.remove("isHidden");
    fullEXIFView.classList.add("isHidden");
  }
};

// Event Listeners
showUploadButton.addEventListener("click", showUploader);
// showThumbnail.addEventListener("click", toggleThumbnail);
showImage.addEventListener("click", toggleImage);
// showBasicEXIF.addEventListener("click", toggleBasicEXIF);
showFullEXIF.addEventListener("click", toggleFullEXIF);
document.querySelector("#navBrand").addEventListener("click", toggleDarkMode);
about.addEventListener("click", showAboutDialog);
geoTag.addEventListener("click", showGPSDialog);
R3Grid.addEventListener("click", toggleR3Grid);
phiGrid.addEventListener("click", togglePhiGrid);
diagonals.addEventListener("click", toggleDiagonals);
AFPoints.addEventListener("click", toggleAFPoints);
SelectedAFPoints.addEventListener("click", toggleSelectedAFPoints);
Highlights.addEventListener("click", toggleHighlights);
Shadows.addEventListener("click", toggleShadows);
showMap.addEventListener("click", toggleMap);
editTags.addEventListener("click", showEditTags);

document
  .querySelector("#previewImageContainer")
  .addEventListener("resize", function () {
    clearTimeout(timeOutFunctionId);
    timeOutFunctionId = setTimeout(resizeCanvas, 500);
  });

altUnit.addEventListener("click", function () {
  const currentState = $(this).text();
  const currentValue = $("#altitude").val();
  if (currentState == "metres") {
    $(this).text("feet");
    if (currentValue) {
      $("#altitude").val((currentValue * 3.28084).toFixed(2));
    }
  } else {
    $(this).text("metres");
    if (currentValue) {
      $("#altitude").val((currentValue / 3.28084).toFixed(2));
    }
  }
});

function showAboutDialog() {
  $("#aboutModal").modal("show");
  $("#aboutVersion").html("1.0.0");
  $("#aboutDate").html("19/04/2024");
  $("#aboutOS").html(detectOS);
  $("#aboutSupported").html("JPEG, Canon (CR2, CR3)");
}
function showGPSDialog() {
  let currentGPS = [];
  getGPSBasic("../" + thisImage.filename, thisImage.endian);
  $("#GPSModal").modal("show");
}

function toggleDarkMode() {
  if (
    document.getElementsByTagName("html")[0].getAttribute("data-bs-theme") ==
    "dark"
  ) {
    document
      .getElementsByTagName("html")[0]
      .setAttribute("data-bs-theme", "light");
  } else {
    document
      .getElementsByTagName("html")[0]
      .setAttribute("data-bs-theme", "dark");
  }
  drawAxes("RGBCanvas");
  drawSingleAxes("LuminanceCanvas");
}

function toggleR3Grid() {
  let canvas;
  let ctx;
  R3Grid.classList.toggle("dropdown-item-checked");

  if (R3Grid.classList.contains("dropdown-item-checked")) {
    canvas = document.getElementById("overlayCanvasR3");
    canvas.width = document.querySelector("#previewImageContainer").clientWidth;
    canvas.height = document.querySelector(
      "#previewImageContainer"
    ).clientHeight;
    drawR3Grid(canvas);
  } else {
    canvas = document.getElementById("overlayCanvasR3");
    canvas.width = document.querySelector("#previewImageContainer").width;
    canvas.height = document.querySelector("#previewImageContainer").height;
    ctx = canvas.getContext("2d");
    ctx.fillStyle = "red";
    ctx.clearRect(0, 0, canvas.height, canvas.width);
  }
}

function togglePhiGrid() {
  let canvas;
  let ctx;
  phiGrid.classList.toggle("dropdown-item-checked");

  if (phiGrid.classList.contains("dropdown-item-checked")) {
    canvas = document.getElementById("overlayCanvasPhi");
    canvas.width = document.querySelector("#previewImageContainer").clientWidth;
    canvas.height = document.querySelector(
      "#previewImageContainer"
    ).clientHeight;
    drawPhiGrid(canvas);
  } else {
    canvas = document.getElementById("overlayCanvasPhi");
    canvas.width = document.querySelector("#previewImageContainer").width;
    canvas.height = document.querySelector("#previewImageContainer").height;
    ctx = canvas.getContext("2d");
    ctx.fillStyle = "red";
    ctx.clearRect(0, 0, canvas.height, canvas.width);
  }
}

function toggleDiagonals() {
  let canvas;
  let ctx;
  diagonals.classList.toggle("dropdown-item-checked");

  if (diagonals.classList.contains("dropdown-item-checked")) {
    canvas = document.getElementById("overlayCanvasDiag");
    canvas.width = document.querySelector("#previewImageContainer").clientWidth;
    canvas.height = document.querySelector(
      "#previewImageContainer"
    ).clientHeight;
    drawDiagonals(canvas);
  } else {
    canvas = document.getElementById("overlayCanvasDiag");
    canvas.width = document.querySelector("#previewImageContainer").width;
    canvas.height = document.querySelector("#previewImageContainer").height;
    ctx = canvas.getContext("2d");
    ctx.fillStyle = "red";
    ctx.clearRect(0, 0, canvas.height, canvas.width);
  }
}

function toggleAFPoints() {
  AFPoints.classList.toggle("dropdown-item-checked");
  SelectedAFPoints.classList.remove("dropdown-item-checked");
  // get AF points from Metadata
  // plot on canvas in red
  // show focussed in green?
  if (AFPoints.classList.contains("dropdown-item-checked")) {
    let AFContext;
    dataToPost = {};
    dataToPost.filename = "../" + thisImage.filename;
    dataToPost.endian = thisImage.endian;
    dataToPost.jpegOffset = thisImage.imageType;
    $.ajax({
      method: "POST",
      async: true,
      url: "../php/EXIF/getAFPoints.php",
      data: dataToPost,
      success: (data) => {
        if (!data) {
          AFPoints.classList.remove("dropdown-item-checked");
          return;
        }
        data = JSON.parse(data);
        AFCanvas = document.querySelector("#overlayCanvasAF");
        AFCanvas.width = document.querySelector(
          "#previewImageContainer"
        ).clientWidth;
        AFCanvas.height = document.querySelector(
          "#previewImageContainer"
        ).clientHeight;
        drawAFPoints(AFCanvas, data);
      },
    });
  } else {
    clearCanvas(AFCanvas);
  }
}

function toggleSelectedAFPoints() {
  SelectedAFPoints.classList.toggle("dropdown-item-checked");
  AFPoints.classList.remove("dropdown-item-checked");
  AFCanvas = document.querySelector("#overlayCanvasAF");
  clearCanvas(AFCanvas);
  if (SelectedAFPoints.classList.contains("dropdown-item-checked")) {
    let AFContext;
    dataToPost = {};
    dataToPost.filename = "../" + thisImage.filename;
    dataToPost.endian = thisImage.endian;
    dataToPost.jpegOffset = thisImage.imageType;
    $.ajax({
      method: "POST",
      async: true,
      url: "../php/EXIF/getAFPoints.php",
      data: dataToPost,
      success: (data) => {
        if (!data) {
          SelectedAFPoints.classList.remove("dropdown-item-checked");
          return;
        }
        data = JSON.parse(data);
        AFCanvas.width = document.querySelector(
          "#previewImageContainer"
        ).clientWidth;
        AFCanvas.height = document.querySelector(
          "#previewImageContainer"
        ).clientHeight;
        drawSelectedAFPoints(AFCanvas, data);
      },
    });
  } else {
    clearCanvas(AFCanvas);
  }
}

function toggleHighlights() {
  Highlights.classList.toggle("dropdown-item-checked");
  HighCanvas = document.querySelector("#overlayCanvasHigh");
  let hiFlashOn;
  if (Highlights.classList.contains("dropdown-item-checked")) {
    let HighContext;
    dataToPost = {};
    dataToPost.filename = "../" + thisImage.filename;
    dataToPost.endian = thisImage.endian;
    if (whites) {
      HighCanvas.width = document.querySelector(
        "#previewImageContainer"
      ).clientWidth;
      HighCanvas.height = document.querySelector(
        "#previewImageContainer"
      ).clientHeight;
      drawHighlightPoints(HighCanvas, whites);
    } else {
      showWorkingMessage(
        "Searching for clipped highlights",
        "rgba(255,255,255,0.5)"
      );
      let HiFlashOn = setInterval(function () {
        document.querySelector("#overlayMessages").classList.toggle("isHidden");
      }, 1000);
      $.ajax({
        method: "POST",
        async: true,
        url: "../php/analyseImage/getHighlights.php",
        data: dataToPost,
        success: (data) => {
          data = JSON.parse(data);
          whites = data;
          HighCanvas.width = document.querySelector(
            "#previewImageContainer"
          ).clientWidth;
          HighCanvas.height = document.querySelector(
            "#previewImageContainer"
          ).clientHeight;
          drawHighlightPoints(HighCanvas, whites);
          hiFlashOn = setInterval(function () {
            HighCanvas.classList.toggle("isHidden");
          }, 1500);
          clearCanvas(document.querySelector("#overlayMessages"));
          document.querySelector("#overlayMessages").classList.add("isHidden");
        },
      });
    }
  } else {
    clearCanvas(HighCanvas);
    HighCanvas.classList.remove("isHidden");
    window.clearInterval(hiFlashOn);
  }
}

function countColours() {
  dataToPost = {};
  dataToPost.filename = "../" + thisImage.filename;
  dataToPost.endian = thisImage.endian;

  $.ajax({
    method: "POST",
    async: true,
    url: "../php/analyseImage/getColours.php",
    data: dataToPost,
    success: (data) => {
      data = JSON.parse(data);
      reds = data[0];
      greens = data[1];
      blues = data[2];
      lumins = data[3];

      let histogramStats = drawHistogram(
        "RGBCanvas",
        reds,
        "rgba(255, 0, 0, 0.1)",
        6
      );
      const totalPixels = histogramStats[0];
      const meanRed = histogramStats[1];
      histogramStats = drawHistogram(
        "RGBCanvas",
        greens,
        "rgba(0, 255, 0, 0.1)",
        43
      );
      const meanGreen = histogramStats[1];
      histogramStats = drawHistogram(
        "RGBCanvas",
        blues,
        "rgba(0, 0, 255, 0.1)",
        80
      );
      const meanBlue = histogramStats[1];
      histogramStats = drawHistogram(
        "LuminanceCanvas",
        lumins,
        "rgba(256,192,128,0.2)",
        6,
        3
      );
      const meanLumos = histogramStats[1];
      const histogramStatsBox = document.querySelector("#HistogramStats");
      histogramStatsBox.innerHTML = `<table id='histogramStatTable' class='table table-sm table-borderless'>
      <tr><td>Pixels</td><td class='text-center'>${totalPixels.toLocaleString()}</td></tr>
      <tr><td>Mean Red</td><td class='text-center'>${meanRed.toFixed(
        1
      )}</td></tr>
      <tr><td>Mean Green</td><td class='text-center'>${meanGreen.toFixed(
        1
      )}</td></tr>
      <tr><td>Mean Blue</td><td class='text-center'>${meanBlue.toFixed(
        1
      )}</td></tr>
      
    </table>`;
    },
  });
}

function toggleMap() {
  showMap.classList.toggle("dropdown-item-checked");
  if (showMap.classList.contains("dropdown-item-checked")) {
    viewOnMap(localStorage.getItem("name"), latitudeG, longitudeG);
  } else {
    document.querySelector(".map").classList.add("isHidden");
  }
}

function toggleShadows() {
  Shadows.classList.toggle("dropdown-item-checked");
  ShadowCanvas = document.querySelector("#overlayCanvasShadow");
  let shadowFlashOn;
  if (Shadows.classList.contains("dropdown-item-checked")) {
    let ShadowContext;
    dataToPost = {};
    dataToPost.filename = "../" + thisImage.filename;
    dataToPost.endian = thisImage.endian;
    if (blacks) {
      ShadowCanvas.width = document.querySelector(
        "#previewImageContainer"
      ).clientWidth;
      ShadowCanvas.height = document.querySelector(
        "#previewImageContainer"
      ).clientHeight;
      drawShadowPoints(ShadowCanvas, blacks);
    } else {
      showWorkingMessage(
        "Searching for clipped shadows",
        "rgba(255,255,255,0.5)"
      );
      let HiFlashOn = setInterval(function () {
        document.querySelector("#overlayMessages").classList.toggle("isHidden");
      }, 1000);
      $.ajax({
        method: "POST",
        async: true,
        url: "../php/analyseImage/getShadows.php",
        data: dataToPost,
        success: (data) => {
          data = JSON.parse(data);
          blacks = data;
          ShadowCanvas.width = document.querySelector(
            "#previewImageContainer"
          ).clientWidth;
          ShadowCanvas.height = document.querySelector(
            "#previewImageContainer"
          ).clientHeight;
          drawShadowPoints(ShadowCanvas, blacks);
          shadowFlashOn = setInterval(function () {
            ShadowCanvas.classList.toggle("isHidden");
          }, 1500);
          clearCanvas(document.querySelector("#overlayMessages"));
          document.querySelector("#overlayMessages").classList.add("isHidden");
        },
      });
    }
  } else {
    clearCanvas(ShadowCanvas);
    ShadowCanvas.classList.remove("isHidden");
    window.clearInterval(shadowFlashOn);
  }
}

function clearCanvas(theCanvas) {
  // theCanvas = document.getElementById("overlayCanvasAF");
  theCanvas.width = document.querySelector("#previewImageContainer").width;
  theCanvas.height = document.querySelector("#previewImageContainer").height;
  AFContext = theCanvas.getContext("2d");
  AFContext.fillStyle = "red";
  AFContext.clearRect(0, 0, theCanvas.height, theCanvas.width);
}
function clearHistogramCanvas(theCanvas) {
  theCanvas = document.getElementById(theCanvas);
  theCanvas.width = document.querySelector("#RGBCanvas").width;
  theCanvas.height = document.querySelector("#RGBCanvas").height;
  const ctx = theCanvas.getContext("2d");
  ctx.fillStyle = "red";
  ctx.clearRect(0, 0, theCanvas.height, theCanvas.width);
}

function drawR3Grid(canvas) {
  let ctx = canvas.getContext("2d");
  ctx.strokeStyle = "white";
  ctx.beginPath();
  ctx.moveTo(0, canvas.height / 3);
  ctx.lineTo(canvas.width, canvas.height / 3);
  ctx.stroke();
  ctx.beginPath();
  ctx.moveTo(0, (canvas.height * 2) / 3);
  ctx.lineTo(canvas.width, (canvas.height * 2) / 3);
  ctx.stroke();
  ctx.beginPath();
  ctx.moveTo(canvas.width / 3, 0);
  ctx.lineTo(canvas.width / 3, canvas.height);
  ctx.stroke();
  ctx.beginPath();
  ctx.moveTo((canvas.width * 2) / 3, 0);
  ctx.lineTo((canvas.width * 2) / 3, canvas.height);
  ctx.stroke();
}

function drawPhiGrid(canvas) {
  let ctx = canvas.getContext("2d");
  let phiWidth = Math.sqrt(5) / 2 + 1.5;
  ctx.strokeStyle = "white";
  ctx.beginPath();
  ctx.moveTo(0, canvas.height / phiWidth);
  ctx.lineTo(canvas.width, canvas.height / phiWidth);
  ctx.stroke();
  ctx.beginPath();
  ctx.moveTo(0, (canvas.height * (phiWidth - 1)) / phiWidth);
  ctx.lineTo(canvas.width, (canvas.height * (phiWidth - 1)) / phiWidth);
  ctx.stroke();
  ctx.beginPath();
  ctx.moveTo(canvas.width / phiWidth, 0);
  ctx.lineTo(canvas.width / phiWidth, canvas.height);
  ctx.stroke();
  ctx.beginPath();
  ctx.moveTo((canvas.width * (phiWidth - 1)) / phiWidth, 0);
  ctx.lineTo((canvas.width * (phiWidth - 1)) / phiWidth, canvas.height);
  ctx.stroke();
}

function drawDiagonals(canvas) {
  let ctx = canvas.getContext("2d");
  ctx.beginPath();
  ctx.moveTo(0, 0);
  ctx.lineTo(canvas.width, canvas.height);
  ctx.strokeStyle = "white";
  ctx.stroke();
  ctx.beginPath();
  ctx.moveTo(0, canvas.height);
  ctx.lineTo(canvas.width, 0);
  ctx.stroke();
}

function drawAFPoints(canvas, data) {
  const scaleX =
    data.AFSize[0] /
    document.querySelector("#previewImageContainer").clientWidth;
  const scaleY =
    data.AFSize[1] /
    document.querySelector("#previewImageContainer").clientHeight;
  const midpointX = data.AFSize[0] / 2 / scaleX;
  const midpointY = data.AFSize[1] / 2 / scaleY;
  let highlightAF = 0;
  if (data["Focussed"] != 0) {
    highlightAF = data["Focussed"];
  } else {
    highlightAF = data["Primary"];
  }
  AFContext = canvas.getContext("2d");
  for (let ix = 0; ix < data.ValidPoints; ix++) {
    if ((highlightAF & Math.pow(2, ix)) == Math.pow(2, ix)) {
      AFContext.strokeStyle = "rgba(255,0,0,1)";
    } else {
      AFContext.strokeStyle = "rgba(0,0,0,1)";
    }
    AFContext.beginPath();
    AFContext.rect(
      midpointX - data["Widths"][ix] / scaleX / 2 + data["X"][ix] / scaleX,
      midpointY - data["Heights"][ix] / scaleY / 2 - data["Y"][ix] / scaleY,
      data["Widths"][ix] / scaleX,
      data["Heights"][ix] / scaleY
    );
    AFContext.stroke();
  }
}

function drawSelectedAFPoints(canvas, data) {
  const scaleX =
    data.AFSize[0] /
    document.querySelector("#previewImageContainer").clientWidth;
  const scaleY =
    data.AFSize[1] /
    document.querySelector("#previewImageContainer").clientHeight;
  const midpointX = data.AFSize[0] / 2 / scaleX;
  const midpointY = data.AFSize[1] / 2 / scaleY;
  let highlightAF = 0;
  if (data["Focussed"] != 0) {
    highlightAF = data["Focussed"];
  } else {
    highlightAF = data["Primary"];
  }
  AFContext = canvas.getContext("2d");
  for (let ix = 0; ix < data.ValidPoints; ix++) {
    if ((highlightAF & Math.pow(2, ix)) == Math.pow(2, ix)) {
      AFContext.strokeStyle = "rgba(255,0,0,1)";
    } else {
      AFContext.strokeStyle = "rgba(0,0,0,0)";
    }
    AFContext.beginPath();
    AFContext.rect(
      midpointX - data["Widths"][ix] / scaleX / 2 + data["X"][ix] / scaleX,
      midpointY - data["Heights"][ix] / scaleY / 2 - data["Y"][ix] / scaleY,
      data["Widths"][ix] / scaleX,
      data["Heights"][ix] / scaleY
    );
    AFContext.stroke();
  }
}

function drawHighlightPoints(canvas, data) {
  const scaleX =
    data[0][0] / document.querySelector("#previewImageContainer").clientWidth;
  const scaleY =
    data[0][1] / document.querySelector("#previewImageContainer").clientHeight;

  HighContext = canvas.getContext("2d");
  HighContext.fillStyle = "rgba(255,0,0,1)";

  for (let ix = 1; ix <= data.length - 1; ix++) {
    HighContext.fillRect(
      data[ix][0] / scaleX + 1,
      data[ix][1] / scaleY + 1,
      1,
      1
    );
  }
}
function drawShadowPoints(canvas, data) {
  const scaleX =
    data[0][0] / document.querySelector("#previewImageContainer").clientWidth;
  const scaleY =
    data[0][1] / document.querySelector("#previewImageContainer").clientHeight;

  ShadowContext = canvas.getContext("2d");
  ShadowContext.fillStyle = "rgba(30,30,255,1)";

  for (let ix = 1; ix <= data.length - 1; ix++) {
    ShadowContext.fillRect(
      data[ix][0] / scaleX + 1,
      data[ix][1] / scaleY + 1,
      1,
      1
    );
  }
}

function detectOS() {
  let userAgent = window.navigator.userAgent,
    platform = window.navigator.platform,
    macosPlatforms = ["Macintosh", "MacIntel", "MacPPC", "Mac68K"],
    windowsPlatforms = ["Win32", "Win64", "Windows", "WinCE"],
    iosPlatforms = ["iPhone", "iPad", "iPod"],
    os = null;

  if (macosPlatforms.indexOf(platform) !== -1) {
    os = "Mac OS";
  } else if (iosPlatforms.indexOf(platform) !== -1) {
    os = "iOS";
  } else if (windowsPlatforms.indexOf(platform) !== -1) {
    os = "Windows";
  } else if (/Android/.test(userAgent)) {
    os = "Android";
  } else if (!os && /Linux/.test(platform)) {
    os = "Linux";
  }
  return os;
}

// text box validations

$("#latitude").on("keydown", function (e) {
  if (e.keyCode >= 48 && e.keyCode <= 57 && e.shiftKey == false) {
    return;
  } //numbers
  if (e.keyCode >= 96 && e.keyCode <= 105 && e.shiftKey == false) {
    return;
  } //number pad
  if (e.keyCode == 190 && this.value.split(".").length <= 1) {
    return;
  } // decimal
  if (e.keyCode == 110 && this.value.split(".").length <= 1) {
    return;
  } // keypad decimal
  if (e.keyCode >= 37 && e.keyCode <= 40) {
    return;
  } //arrows
  if (e.keyCode == 8 || e.keyCode == 12 || e.keyCode == 46) {
    return;
  } // backspace and delete
  if ((e.keyCode == 109 || e.keyCode == 189) && this.selectionStart == 0) {
    return;
  } // dash/minus but only as first character
  if ((e.keyCode == 9 || e.keyCode == 13) && e.shiftKey == false) {
    document.querySelector("#longitude").focus();
  }
  if ((e.keyCode == 9 || e.keyCode == 13) && e.shiftKey == true) {
    document.querySelector("#timeStamp").focus();
  }
  e.preventDefault();
});
$("#latitude").on("blur", function (e) {
  if (this.value > 90) {
    this.value = 90;
  }
  if (this.value < -90) {
    this.value = -90;
  }
});
$("#longitude").on("keydown", function (e) {
  if (e.keyCode >= 48 && e.keyCode <= 57 && e.shiftKey == false) {
    return;
  } //numbers
  if (e.keyCode >= 96 && e.keyCode <= 105 && e.shiftKey == false) {
    return;
  } //number pad
  if (e.keyCode == 190 && this.value.split(".").length <= 1) {
    return;
  } // decimal
  if (e.keyCode == 110 && this.value.split(".").length <= 1) {
    return;
  } // keypad decimal
  if (e.keyCode >= 37 && e.keyCode <= 40) {
    return;
  } //arrows
  if (e.keyCode == 8 || e.keyCode == 12 || e.keyCode == 46) {
    return;
  } // backspace and delete
  if ((e.keyCode == 109 || e.keyCode == 189) && this.selectionStart == 0) {
    return;
  } // dash/minus but only as first character
  if ((e.keyCode == 9 || e.keyCode == 13) && e.shiftKey == false) {
    document.querySelector("#altitude").focus();
  }
  if ((e.keyCode == 9 || e.keyCode == 13) && e.shiftKey == true) {
    document.querySelector("#latitude").focus();
  }
  e.preventDefault();
});
$("#longitude").on("blur", function (e) {
  if (this.value > 180) {
    this.value = 180;
  }
  if (this.value < -180) {
    this.value = -180;
  }
});
$("#altitude").on("keydown", function (e) {
  if (e.keyCode >= 48 && e.keyCode <= 57 && e.shiftKey == false) {
    return;
  } //numbers
  if (e.keyCode >= 96 && e.keyCode <= 105 && e.shiftKey == false) {
    return;
  } //number pad
  if (e.keyCode == 190 && this.value.split(".").length <= 1) {
    return;
  } // decimal
  if (e.keyCode == 110 && this.value.split(".").length <= 1) {
    return;
  } // keypad decimal
  if (e.keyCode >= 37 && e.keyCode <= 40) {
    return;
  } //arrows
  if (e.keyCode == 8 || e.keyCode == 12 || e.keyCode == 46) {
    return;
  } // backspace and delete
  if ((e.keyCode == 109 || e.keyCode == 189) && this.selectionStart == 0) {
    return;
  } // dash/minus but only as first character
  if ((e.keyCode == 9 || e.keyCode == 13) && e.shiftKey == false) {
    document.querySelector("#dateStamp").focus();
  }
  if ((e.keyCode == 9 || e.keyCode == 13) && e.shiftKey == true) {
    document.querySelector("#longitude").focus();
  }
  e.preventDefault();
});

function getGPSBasic(filename, endian) {
  data = "nilk";
  dataToPost = {};
  dataToPost.filename = filename;
  dataToPost.endian = endian;
  $.ajax({
    method: "POST",
    async: true,
    url: "../php/GPS/basicGPS.php",
    data: dataToPost,
    success: (data) => {
      data = JSON.parse(data);
      if (data["Lat Coords"]) {
        document.querySelector("#latitude").value = data["Lat Coords"];
      } else {
        document.querySelector("#latitude").value = "";
      }
      if (data["Long Coords"]) {
        document.querySelector("#longitude").value = data["Long Coords"];
      } else {
        document.querySelector("#longitude").value = "";
      }
      if (data["Altitude Coords"]) {
        document.querySelector("#altitude").value = data["Altitude Coords"];
      } else {
        document.querySelector("#altitude").value = "";
      }
      if (data["Date Stamp" != ""]) {
        document.querySelector("#dateStamp").value = data["Date Stamp"];
      } else {
        document.querySelector("#dateStamp").value = new Date()
          .toISOString()
          .split("T")[0];
      }
      if (data["Date Stamp" != "0:0:0"]) {
        document.querySelector("#timeStamp").value = data["Time Stamp"];
      } else {
        document.querySelector("#timeStamp").value = new Date()
          .toTimeString()
          .substring(0, 8);
      }
    },
  });
}

$("#addGPStoOriginal").on("click", function () {
  // latitude and longitude must exist
  // latitude limit is -90 to 90
  // longitude limit is -180 to 180
  // if ticked altitude must be a number
  // if ticked date stamp must be a valid date
  // if ticked time stamp must be a valid time
  dataToPost = {};
  dataToPost.filename = "../" + thisImage.filename;
  dataToPost.endian = thisImage.endian;
  dataToPost.latitude = document.querySelector("#latitude").value;
  dataToPost.longitude = document.querySelector("#longitude").value;
  dataToPost.altitude = document.querySelector("#altitude").value;
  dataToPost.datestamp = document.querySelector("#dateStamp").value;
  dataToPost.timestamp = document.querySelector("#timeStamp").value;
  dataToPost.include_altitude = document.querySelector("#inc_altitude").checked;
  dataToPost.include_date = document.querySelector("#inc_date").checked;
  dataToPost.include_time = document.querySelector("#inc_time").checked;
  $.ajax({
    method: "POST",
    async: true,
    url: "../php/GPS/addGPSToOriginal.php",
    data: dataToPost,
    success: (data) => {
      // data = JSON.parse(data);
      console.log(data);
    },
  });
});

$("#RGBHistogram").on("click", function () {});

function showWorkingMessage(messageText, colour) {
  let messageCanvas = document.querySelector("#overlayMessages");
  messageCanvas.classList.remove("isHidden");
  messageCanvas.width = document.querySelector(
    "#previewImageContainer"
  ).clientWidth;
  messageCanvas.height = document.querySelector(
    "#previewImageContainer"
  ).clientHeight;

  const ctx = messageCanvas.getContext("2d");
  ctx.font = "48px sans-serif";
  ctx.textAlign = "center";
  ctx.fillStyle = colour;
  ctx.fillText(messageText, messageCanvas.width / 2, messageCanvas.height / 2);
}

function resizeCanvas() {
  const newWidth = document.querySelector("#previewImageContainer").clientWidth;
  const newHeight = document.querySelector(
    "#previewImageContainer"
  ).clientHeight;

  document.querySelector("#overlayMessages").width = newWidth;
  document.querySelector("#overlayMessages").height = newHeight;
  if (R3Grid.classList.contains("dropdown-item-checked")) {
    document.querySelector("#overlayCanvasR3").width = newWidth;
    document.querySelector("#overlayCanvasR3").height = newHeight;
    drawR3Grid(document.querySelector("#overlayCanvasR3"));
  }
  if (diagonals.classList.contains("dropdown-item-checked")) {
    document.querySelector("#overlayCanvasDiag").width = newWidth;
    document.querySelector("#overlayCanvasDiag").height = newHeight;
    drawDiagonals(document.querySelector("#overlayCanvasDiag"));
  }
  if (phiGrid.classList.contains("dropdown-item-checked")) {
    document.querySelector("#overlayCanvasPhi").width = newWidth;
    document.querySelector("#overlayCanvasPhi").height = newHeight;
    drawPhiGrid(document.querySelector("#overlayCanvasPhi"));
  }
  if (AFPoints.classList.contains("dropdown-item-checked")) {
    document.querySelector("#overlayCanvasAF").width = newWidth;
    document.querySelector("#overlayCanvasAF").height = newHeight;
    let AFContext;
    dataToPost = {};
    dataToPost.filename = "../" + thisImage.filename;
    dataToPost.endian = thisImage.endian;
    dataToPost.jpegOffset = thisImage.imageType;
    $.ajax({
      method: "POST",
      async: true,
      url: "../php/EXIF/getAFPoints.php",
      data: dataToPost,
      success: (data) => {
        if (!data) {
          AFPoints.classList.remove("dropdown-item-checked");
          return;
        }
        data = JSON.parse(data);
        AFCanvas = document.querySelector("#overlayCanvasAF");
        drawAFPoints(AFCanvas, data);
      },
    });
  }
  if (SelectedAFPoints.classList.contains("dropdown-item-checked")) {
    document.querySelector("#overlayCanvasAF").width = newWidth;
    document.querySelector("#overlayCanvasAF").height = newHeight;
    let AFContext;
    dataToPost = {};
    dataToPost.filename = "../" + thisImage.filename;
    dataToPost.endian = thisImage.endian;
    dataToPost.jpegOffset = thisImage.imageType;
    $.ajax({
      method: "POST",
      async: true,
      url: "../php/EXIF/getAFPoints.php",
      data: dataToPost,
      success: (data) => {
        if (!data) {
          SelectedAFPoints.classList.remove("dropdown-item-checked");
          return;
        }
        data = JSON.parse(data);
        drawSelectedAFPoints(AFCanvas, data);
      },
    });
  }

  if (Highlights.classList.contains("dropdown-item-checked")) {
    HighCanvas = document.querySelector("#overlayCanvasHigh");
    HighCanvas.width = newWidth;
    HighCanvas.height = newHeight;
    let hiFlashOn;
    let HighContext;
    dataToPost = {};
    dataToPost.filename = "../" + thisImage.filename;
    dataToPost.endian = thisImage.endian;
    if (whites) {
      drawHighlightPoints(HighCanvas, whites);
    } else {
      showWorkingMessage(
        "Searching for clipped highlights",
        "rgba(255,255,255,0.5)"
      );
      $.ajax({
        method: "POST",
        async: true,
        url: "../php/analyseImage/getHighlights.php",
        data: dataToPost,
        success: (data) => {
          data = JSON.parse(data);
          whites = data;
          drawHighlightPoints(HighCanvas, whites);
          hiFlashOn = setInterval(function () {
            HighCanvas.classList.toggle("isHidden");
          }, 1500);
          clearCanvas(document.querySelector("#overlayMessages"));
          document.querySelector("#overlayMessages").classList.add("isHidden");
        },
      });
    }
  }
  ShadowCanvas = document.querySelector("#overlayCanvasShadow");
  ShadowCanvas.width = newWidth;
  ShadowCanvas.height = newHeight;
  let shadowFlashOn;
  if (Shadows.classList.contains("dropdown-item-checked")) {
    let ShadowContext;
    dataToPost = {};
    dataToPost.filename = "../" + thisImage.filename;
    dataToPost.endian = thisImage.endian;
    if (blacks) {
      ShadowCanvas.width = document.querySelector(
        "#previewImageContainer"
      ).clientWidth;
      ShadowCanvas.height = document.querySelector(
        "#previewImageContainer"
      ).clientHeight;
      drawShadowPoints(ShadowCanvas, blacks);
    } else {
      showWorkingMessage(
        "Searching for clipped shadows",
        "rgba(255,255,255,0.5)"
      );
      $.ajax({
        method: "POST",
        async: true,
        url: "../php/analyseImage/getShadows.php",
        data: dataToPost,
        success: (data) => {
          data = JSON.parse(data);
          blacks = data;
          ShadowCanvas.width = document.querySelector(
            "#previewImageContainer"
          ).clientWidth;
          ShadowCanvas.height = document.querySelector(
            "#previewImageContainer"
          ).clientHeight;
          drawShadowPoints(ShadowCanvas, blacks);
          shadowFlashOn = setInterval(function () {
            ShadowCanvas.classList.toggle("isHidden");
          }, 1500);
          clearCanvas(document.querySelector("#overlayMessages"));
          document.querySelector("#overlayMessages").classList.add("isHidden");
        },
      });
    }
  }
}

function drawSingleAxes(histogramCanvas) {
  const graphCanvas = document.querySelector("#" + histogramCanvas);
  const ctx = graphCanvas.getContext("2d");
  ctx.beginPath();
  ctx.strokeStyle = "gray";
  ctx.moveTo(15, 5);
  ctx.lineTo(15, 104);
  ctx.stroke();
  ctx.moveTo(15, 104);
  ctx.lineTo(272, 104);
  ctx.stroke();
  ctx.save();
  ctx.font = "16px Arial";
  ctx.translate(11, 56);
  ctx.rotate(-Math.PI / 2);
  ctx.textAlign = "center";
  if (
    document.getElementsByTagName("html")[0].getAttribute("data-bs-theme") ==
    "dark"
  ) {
    ctx.fillStyle = "yellow";
  } else {
    ctx.fillStyle = "darkblue";
  }
  ctx.fillText("Luminance", 0, 0);
  ctx.restore();
}

function drawAxes(histogramCanvas) {
  const graphCanvas = document.querySelector("#" + histogramCanvas);
  const ctx = graphCanvas.getContext("2d");
  ctx.beginPath();
  ctx.strokeStyle = "gray";
  ctx.moveTo(15, 5);
  ctx.lineTo(15, 110);
  ctx.stroke();
  ctx.beginPath();
  ctx.moveTo(15, 38);
  ctx.lineTo(272, 38);
  ctx.stroke();
  ctx.beginPath();
  ctx.moveTo(15, 75);
  ctx.lineTo(272, 75);
  ctx.stroke();
  ctx.beginPath();
  ctx.moveTo(15, 112);
  ctx.lineTo(272, 112);
  ctx.stroke();
  ctx.save();
  ctx.font = "10px Arial";
  ctx.translate(11, 22);
  ctx.rotate(-Math.PI / 2);
  ctx.textAlign = "center";
  if (
    document.getElementsByTagName("html")[0].getAttribute("data-bs-theme") ==
    "dark"
  ) {
    ctx.fillStyle = "yellow";
  } else {
    ctx.fillStyle = "darkblue";
  }
  ctx.fillText("Red", 0, 0);
  ctx.translate(-34, 0);
  ctx.fillText("Green", 0, 0);
  ctx.translate(-40, 0);
  ctx.fillText("Blue", 0, 0);
  ctx.restore();
}

function drawHistogram(
  histogramCanvas,
  colour,
  rgbColour,
  offset = 0,
  multiplier = 1
) {
  const maxColour = Math.max(...colour);
  let SecondHighest = Math.max(
    ...colour.filter((x) => x !== Math.max(...colour))
  );
  if (SecondHighest == 0) {
    SecondHighest = maxColour;
  }

  const graphCanvas = document.querySelector("#" + histogramCanvas);
  let ctx = graphCanvas.getContext("2d");
  ctx.strokeStyle = rgbColour;
  ctx.beginPath();
  let sumUp = 0;
  let multUp = 0;
  for (let ix = 0; ix < 256; ix++) {
    ctx.moveTo(18 + ix, multiplier * 32 + offset);
    let height = (colour[ix] / SecondHighest) * 32 * multiplier;
    ctx.lineTo(18 + ix, multiplier * 32 + offset - height);
    ctx.stroke();
    multUp += ix * colour[ix];
    sumUp += colour[ix];
  }
  return [sumUp, multUp / sumUp];
}

function showEditTags() {
  $("#cameraMake").val(editableTags.make);
  $("#cameraModel").val(editableTags.model);
  $("#lensModel").val(editableTags.lens);
  $("#description").val(editableTags.description);
  $("#artist").val(editableTags.artist);
  $("#copyright").val(editableTags.copyright);
  $("#rating").val(editableTags.rating);
  $("#ratingPercent").val(editableTags.ratingPercent);
  $("#userComment").val(editableTags.comment);
  $("#shutter").val(editableTags.shutter);
  $("#aperture").val(editableTags.fnumber);
  $("#ISO").val(editableTags.ISO);

  $("#editTagsModal").modal("show");
}

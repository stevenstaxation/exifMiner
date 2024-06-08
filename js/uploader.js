const dropArea = document.getElementById("dropZone");
var thisImage;
var shutterSpeed;
var aperture;
var sensitivity;
var flash;
var focalLength;

let fileList;
let oldThumbSource;
let oldImageSource;
let oldSource = "";

dropArea.addEventListener("dragover", (event) => {
  event.stopPropagation();
  event.preventDefault();
  event.dataTransfer.dropEffect = "copy";
});

dropArea.addEventListener("drop", (event) => {
  event.stopPropagation();
  event.preventDefault();
  fileList = event.dataTransfer.files;
  customFile.files = fileList;
  const selectedFile = fileList[0]["name"];
  localStorage.setItem("name", fileList[0]["name"]);
  localStorage.setItem("size", fileList[0]["size"]);
  localStorage.setItem("modified", fileList[0]["lastModifiedDate"]);

  const selectedType = getExtensionInfo(
    selectedFile.split(".").pop().toUpperCase()
  );

  // get old image and thumbnail src
  // so they can be deleted
  const oldThumb = document
    .querySelector(".thumbnailView")
    .getElementsByTagName("img")[0];
  if (oldThumb) {
    oldThumbSource = oldThumb.getAttribute("src");
  }
  const oldImage = document
    .querySelector(".imageView")
    .getElementsByTagName("img")[0];
  if (oldImage) {
    oldImageSource = oldImage.getAttribute("src");
  }
  if (oldThumbSource) {
    deleteFile(oldThumbSource);
    deleteFile(oldSource);
  }
  if (oldImageSource) {
    deleteFile(oldImageSource);
  }

  uploadFile();
});

function getExtensionInfo(extension) {
  for (x = 0; x < extensionTable.length; x++) {
    if (extensionTable[x][0] == extension) return extensionTable[x][1];
  }
  return "<div class='alert alert-danger ml-auto mr-auto'>This type of file is not supported.</div>";
}

function _(el) {
  return document.getElementById(el);
}

async function uploadFile() {
  const file = _("customFile").files[0];
  let formData = new FormData();
  formData.append("customFile", file);
  const ajax = new XMLHttpRequest();
  ajax.upload.addEventListener("progress", progressHandler, false);
  ajax.addEventListener("load", completeHandler, false);
  ajax.addEventListener("error", errorHandler, false);
  ajax.addEventListener("abort", abortHandler, false);
  ajax.open("POST", "../php/upload.php");
  ajax.send(formData);
}

function progressHandler(event) {
  var percent = (event.loaded / event.total) * 100;

  _("uploadProgress").innerHTML =
    "<div id='uploadProgress' class='progress-bar progress-bar-striped progress-bar-animated bg-success container' role='progressbar' aria-valuenow='" +
    Math.round(percent) +
    "' aria-valuemin='0' aria-valuemax='100'>" +
    Math.round(percent) +
    "%</div>";
}

function completeHandler(event) {
  _("uploadProgress").innerHTML = "";

  const viewMenu = document.querySelectorAll(".viewMenu");
  viewMenu.forEach((mnuItem) => mnuItem.classList.remove("disabled"));

  thisImage = new EXIFImage(fileList[0]);

  if (thisImage.imageType == 0) {
    alert("Not supported");
    return;
  }

  thisImage.getPointerToThumbnail();
  thisImage.getPointerToImage();
  if (thisImage.image == "") {
    thisImage.image = thisImage.thumbnail;
  }
  thisImage.buildEXIF();

  // get image
  document.querySelector(
    ".imageView"
  ).innerHTML = `<div class="imagePreview_Wrapper" width='100%'>
  <img id='previewImageContainer' src="${thisImage.image}" width='60%'/> 
  
  <canvas id='overlayCanvasR3'></canvas>
  <canvas id='overlayCanvasDiag'></canvas>
  <canvas id='overlayCanvasAF'></canvas>
  <canvas id='overlayCanvasPhi'></canvas>
  <canvas id='overlayCanvasHigh'></canvas>
  <canvas id='overlayCanvasShadow'></canvas>
  <canvas id='overlayMessages'></canvas>
  
  </div>`;

  // get thumbnail
  document.querySelector(
    ".thumbnailView"
  ).innerHTML = `<div class="container"><img src="${thisImage.thumbnail}" width='40%'/></div>`;
  // basic EXIF
  document.querySelector(
    ".basicEXIFView"
  ).innerHTML = `<div class="container">${thisImage.basicEXIF}</div>`;

  const clickEvent = new Event("click");
  document.querySelector("#showUploader").dispatchEvent(clickEvent);
  document.querySelector(".imageView").classList.remove("isHidden");
  document.querySelector("#showImage").classList.add("dropdown-item-checked");
  document.querySelector(".basicEXIFView").classList.remove("isHidden");
  document.querySelector(".fullEXIFView").classList.add("isHidden");
  document.querySelector("#map").classList.add("isHidden");
  let ro = new ResizeObserver(resizeCanvas).observe(imageView);

  // document
  //   .querySelector("#showBasicTags")
  //   .classList.add("dropdown-item-checked");

  // are any options already checked?
  document
    .querySelector("#showAFPoints")
    .classList.remove("dropdown-item-checked");
  document
    .querySelector("#showSelectedAFPoints")
    .classList.remove("dropdown-item-checked");
  document
    .querySelector("#showHighlights")
    .classList.remove("dropdown-item-checked");
  document
    .querySelector("#showShadows")
    .classList.remove("dropdown-item-checked");

  document.querySelector("#R3Grid").classList.remove("dropdown-item-checked");
  document.querySelector("#PhiGrid").classList.remove("dropdown-item-checked");
  document
    .querySelector("#Diagonals")
    .classList.remove("dropdown-item-checked");
  document
    .querySelector("#showAllTags")
    .classList.remove("dropdown-item-checked");
  document.querySelector("#showMap").classList.remove("dropdown-item-checked");

  whites = null;
  blacks = null;
  countColours();

  // draw histogram axes
  clearHistogramCanvas("RGBCanvas");
  clearHistogramCanvas("LuminanceCanvas");

  drawAxes("RGBCanvas");
  drawSingleAxes("LuminanceCanvas");
  document.querySelector("#histogramsBox").classList.remove("isHidden");
  // document.querySelector("#HLuminanceCanvas").classList.remove("isHidden");
}

function errorHandler(event) {
  _("#uploadProgress").innerHTML = "Upload Failed";
  const viewMenu = document.querySelectorAll(".viewMenu");
  viewMenu.forEach((mnuItem) => mnuItem.classList.add("disabled"));
}

function abortHandler(event) {
  _("#uploadProgress").innerHTML = "Upload Aborted";
  const viewMenu = document.querySelectorAll(".viewMenu");
  viewMenu.forEach((mnuItem) => mnuItem.classList.add("disabled"));
}

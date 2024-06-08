class EXIFImage {
  #private = [];

  constructor(upload) {
    this.filename = "../uploads/" + upload["name"];
    this.lastModified = upload["lastModifiedDate"];
    this.fileSize = upload["size"];
    this.imageType = this.identifyFile();
  }

  get name() {
    return this._name;
  }
  set name(name) {
    this._name = name;
  }

  get imageType() {
    return this._imageType;
  }
  set imageType(imageType) {
    this._imageType = imageType;
  }

  get imageDescription() {
    switch (this.imageType) {
      case 0:
        return "unsupported image";
      case 1:
        return "JPEG Image";
      case 2:
        return "JPEG Image with JFIF";
      case 3:
        return "JPEG Image with EXIF";
      case 4:
        return "JPEG Image with SPIFF";
      case 9:
        return "Canon RAW Image (CR2)";
      case 10:
        return "Canon RAW Image (CR3)";
    }
  }
  get thumbnail() {
    return this._thumbnail;
  }

  set thumbnail(thumbPointer) {
    this._thumbnail = thumbPointer;
  }

  get image() {
    return this._image;
  }

  set image(imagePointer) {
    this._image = imagePointer;
  }

  get fileSize() {
    return this._fileSize;
  }
  set fileSize(size) {
    this._fileSize = size;
  }
  get fileExtension() {
    return this.filename.slice(
      ((this.filename.lastIndexOf(".") - 1) >>> 0) + 2
    );
  }

  get lastModified() {
    return this._lastModified;
  }
  set lastModified(date) {
    this._lastModified = date;
  }

  get Private() {
    return this.#private;
  }

  get endian() {
    if (this._endian) {
      return this._endian;
    }
  }

  set endian(endian) {
    this._endian = endian;
  }

  get exposure() {}

  get EXIF_All() {}

  identifyFile() {
    let dataToPost = {};
    let signature = "";
    dataToPost.filename = this.filename;
    $.ajax({
      async: false,
      url: "../php/getFileSignature.php",
      data: dataToPost,
      type: "GET",
      success: function (data) {
        signature = data;
      },
    });
    signature = signature.toUpperCase();

    // is it JPEG
    let sigSlice1 = signature.slice(0, 6);
    let sigSlice2 = signature.slice(6, 8);
    let sigSlice3 = signature.slice(24, 28);
    if (sigSlice1 == "FFD8FF") {
      if (sigSlice3 == "4D4D") {
        this.endian = "big";
      } else {
        this.endian = "little";
      }
      switch (sigSlice2) {
        case "E0":
          return 2;
        case "E1":
          return 3;
        case "E8":
          return 4;
        default:
          return 1;
      }
    }

    // is it Canon CR2 (regular Canon)
    sigSlice1 = signature.slice(0, 8);
    sigSlice2 = signature.slice(16, 24);

    if (sigSlice1 === "49492A00" && sigSlice2 === "43520200") {
      this.endian = "little";
      return 9;
    }
    if (sigSlice1 === "4D4D002A" && sigSlice2 === "43520200") {
      this.endian = "big";
      return 9;
    }

    // is it Canon CR3 (new style Canon)
    sigSlice1 = signature.slice(0, 8);
    if (sigSlice1 === "66747970") {
      this.endian = "little";
      return 10;
    }

    this.endian = "little";
    return 0;
  }

  getPointerToThumbnail() {
    let dataToPost = {};
    let thumbURL = "";

    switch (this.imageType) {
      case 1:
      case 2:
      case 3:
      case 4:
        this.thumbnail = this.filename;
        return;
      case 9:
      case 18:
        thumbURL = "../php/getThumbnails/IFD1-201.php";
        break;
    }

    dataToPost.filename = "../" + this.filename;
    dataToPost.endian = this.endian;
    $.ajax({
      method: "POST",
      async: false,
      url: thumbURL,
      data: dataToPost,
      success: (data) => {
        data = data.slice(3);
        this.thumbnail = data;
      },
    });
  }

  getPointerToImage() {
    let dataToPost = {};
    let imageURL = "";

    switch (this.imageType) {
      case 1:
      case 2:
      case 3:
      case 4:
        this.image = this.filename;
        return;
      case 9:
      case 10:
        imageURL = "../php/getImages/IFD0-111.php";
        break;
    }

    dataToPost.filename = "../" + this.filename;
    dataToPost.endian = this.endian;
    $.ajax({
      type: "POST",
      async: false,
      url: imageURL,
      data: dataToPost,
      success: (data) => {
        data = data.slice(3);
        this.image = data;
      },
    });
  }

  buildEXIF() {
    let dataToPost = {};
    let EXIFURL = "";

    switch (this.imageType) {
      case 1:
      case 2:
      case 3:
      case 4:
        EXIFURL = "../php/EXIF/jpegEXIF.php";
        break;
      case 9:
      case 10:
        EXIFURL = "../php/EXIF/basicEXIF.php";
        break;
    }

    dataToPost.filename = "../" + this.filename;
    dataToPost.endian = this.endian;
    $.ajax({
      type: "POST",
      async: false,
      url: EXIFURL,
      data: dataToPost,
      success: (data) => {
        data = JSON.parse(data);
        // set size format
        let fileSize = new Intl.NumberFormat("en-GB", {
          maximumFractionDigits: Math.max(
            localStorage.getItem("byteSize") - 1,
            0
          ),
        }).format(
          new Number(
            localStorage.getItem("size") /
              Math.pow(1000, localStorage.getItem("byteSize"))
          )
        );
        // save filename for deletion when next is uploaded
        oldSource = String(data["filename"]).substring(3);

        // Image Info
        let returnData = `<h3 class='sectionTitle'>Basic Image Information</h3><table class='table table-sm'>
        <tr class='table-info'><th>Image metadata</th><th></th></tr><tr><td class='w-50'>Filename:</td><td>${localStorage.getItem(
          "name"
        )}</td></tr><tr><td>File Size:</td><td>${fileSize} ${getFileSizeSuffix()}</td></tr><tr><td>Image Type:</td><td>${
          this.imageDescription
        }</td></tr><tr><td>MIME Type:</td><td>${
          data["MIME Type"]
        }</td></tr><tr><td>Image Size:</td><td>${data["Width"]} x ${
          data["Height"]
        }</td></tr><tr><td>Date taken:</td><td>${
          data["Create Date"]
        }</td></tr></table>`;

        // Exposure Info
        shutterSpeed = data["Exposure Time"];
        editableTags.shutter = shutterSpeed;
        aperture = data["Aperture"];
        editableTags.fnumber = "ƒ/" + aperture;
        sensitivity = data["ISO"];
        editableTags.ISO = sensitivity;
        focalLength = data["Focal Length"];
        flash = data["Flash"];

        returnData =
          returnData +
          `<table class='table table-sm'>
          <tr class='table-info'><th>Exposure settings</th><th></th></tr>
          <tr><td class='w-50'>Exposure Time:</td><td>${data["Exposure Time"]}</td></tr>
          <tr><td>Aperture:</td><td>ƒ/${data["Aperture"]}</td></tr>
          <tr><td>ISO:</td><td>${data["ISO"]}</td></tr>
          <tr><td>Focal Length:</td><td>${data["Focal Length"]}`;
        if (data["Focal Length 35"]) {
          returnData =
            returnData +
            ` (in 35mm format = ${data["Focal Length 35"]})</td></tr>`;
        } else {
          returnData = returnData + `</td></tr>`;
        }
        returnData =
          returnData + ` <tr><td>Flash:</td><td>${data["Flash"]}</table>`;

        // Equipment
        returnData =
          returnData +
          `<table class='table table-sm'><tr class='table-info'><th>Equipment</th><th></th></tr><tr><td class='w-50'>Make:</td><td>${data["Make"]}</td></tr><tr><td>Model:</td><td>${data["Model"]}</td></tr><tr><td>Lens Model:</td><td>${data["Lens Model"]}</td></tr></table>`;

        editableTags.make = data["Make"];
        editableTags.model = data["Model"];
        editableTags.lens = data["Lens Model"];
        editableTags.artist = data["Artist"];
        editableTags.copyright = data["Copyright"];
        editableTags.comment = data["Comment"];

        latitudeG = null;
        longitudeG = null;
        //GPS Location
        if (data["GPSExists"]) {
          let latitude = 0;
          let longitude = 0;
          let altitudeDir = "";
          let altitude = data["Altitude Coords"][0];

          latitudeG =
            parseFloat(data["Lat Coords"][0]) +
            parseFloat(data["Lat Coords"][1]) / 60 +
            parseFloat(data["Lat Coords"][2]) / 3600;

          longitudeG =
            parseFloat(data["Long Coords"][0]) +
            parseFloat(data["Long Coords"][1]) / 60 +
            parseFloat(data["Long Coords"][2]) / 3600;

          if (localStorage.getItem("GPS_Coords") == 1) {
            latitude = `${data["Lat Coords"][0]}° ${
              data["Lat Coords"][1]
            }' ${data["Lat Coords"][2].toFixed(3)} ${data["Latitude Dir"]}`;
            longitude = `${data["Long Coords"][0]}° ${
              data["Long Coords"][1]
            }' ${data["Long Coords"][2].toFixed(3)} ${data["Longitude Dir"]}`;
          } else {
            latitude = (
              parseFloat(data["Lat Coords"][0]) +
              parseFloat(data["Lat Coords"][1]) / 60 +
              parseFloat(data["Lat Coords"][2]) / 3600
            ).toFixed(8);
            if (data["Latitude Dir"] == "S") {
              latitude = latitude * -1;
              latitudeG = latitudeG * -1;
            }
            longitude = (
              parseFloat(data["Long Coords"][0]) +
              parseFloat(data["Long Coords"][1]) / 60 +
              parseFloat(data["Long Coords"][2]) / 3600
            ).toFixed(8);
            if (data["Longitude Dir"] == "W") {
              longitude = longitude * -1;
              longitudeG = longitudeG * -1;
            }
          }
          if (data["Altitude Dir"] == 0) {
            altitudeDir = "above sea level";
          } else if (data["Altitude Dir"] == 1) {
            altitudeDir = "below sea level";
          }

          if (altitude) {
            if (localStorage.getItem("Altitude_Distance_Unit") == "1") {
              altitude =
                new Intl.NumberFormat("en-GB", {
                  maximumFractionDigits: 2,
                }).format(altitude * 3.280839895) + " feet";
            } else {
              altitude =
                new Intl.NumberFormat("en-GB", {
                  maximumFractionDigits: 2,
                }).format(altitude) + "m";
            }
          } else {
            altitude = "not recorded";
            altitudeDir = "";
          }

          returnData =
            returnData +
            `<table class='table table-sm'><tr class='table-info'><th>Location</th><th></th></tr><tr><td class='w-50'>Latitude:</td><td>${latitude}</td>
            </tr>
              <tr>
                <td>Longitude:</td><td>${longitude}</td></tr><tr><td>Altitude:</td><td>${altitude} ${altitudeDir}</td></tr><tr><td></td><td>
                  <div class='btn btn-success btn-sm' onclick='viewOnMap("${localStorage.getItem(
                    "name"
                  )}", ${latitude},${longitude})'>view on map</div>
                </td>
              </tr>
            </table>`;
        } else {
          document.querySelector("#showMap").classList.add("disabled");
        }

        // User info
        returnData =
          returnData +
          `<table class='table table-sm'><tr class='table-info'><th>User details</th><th></th></tr><tr><td class='w-50'>User:</td><td>${data["Artist"]}</td></tr><tr><td>Copyright:</td><td>${data["Copyright"]}</td></tr><tr><td>Comment:</td><td>${data["Comment"]}</td></tr></table>`;

        if (data["Rating"]) {
          editableTags.rating = data["Rating"];
        }
        if (data["Rating Percent"]) {
          editableTags.ratingPercent = data["Rating Percent"];
        }

        this.basicEXIF = returnData;
      },
    });
  }
}

let map;

function viewOnMap(filename, latitude, longitude) {
  showMap.classList.add("dropdown-item-checked");
  document.querySelector("#map").classList.remove("isHidden");
  initMap(filename, latitude, longitude);
}

async function initMap(filename, latitude, longitude) {
  const { Map } = await google.maps.importLibrary("maps");

  const map = new google.maps.Map(document.getElementById("map"), {
    zoom: 13,
    center: { lat: latitude, lng: longitude },
    mapId: "DEMO_MAP_ID",
  });

  const iconName =
    thisImage.filename.substring(0, thisImage.filename.length - 4) +
    "-thumb.jpg";

  const marker = new google.maps.marker.AdvancedMarkerElement({
    map,
    position: { lat: latitude, lng: longitude },
    title: filename,
  });

  let infoWindow = new google.maps.InfoWindow({
    headerContent: filename,
    content: `
    <div style='float:left'>
      <img src='${iconName}'>
    </div>
    <div style='float: right; padding: 10px; text-align: right;font-size: 75%'>
      Shutter: ${shutterSpeed}<br>
      Aperture: f/${aperture}<br>
      Sensitivity: ${sensitivity}<br>
      Focal Length: ${focalLength}<br>
      Flash: ${flash}
    </div>`,
  });
  infoWindow.open(map, marker);
}

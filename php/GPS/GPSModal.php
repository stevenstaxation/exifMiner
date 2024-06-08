<div class="modal fade" id="GPSModal" tabindex="-1" aria-labelledby="GPSModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-md">
    <div class="modal-content">
      <div class="modal-header">
        <img src='../../assets/cameraLogo.png' width=15%/>
        <h3 class="modal-title" id="GPSModalLabel" style='padding-top: 10px; padding-left:20px'><b>Geo Tag</b></h3>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Enter the details you want to add to your image<p>
        <table class='table table-borderless'>
          <tr>
            <td class='w-40'>
                <label for="latitude" class="form-label mt-2">Latitude (decimal)</label>
            </td>
            <td class='w-10'></td>
            <td class="w-50">
                <div class="input-group">
                    <input type='text' class="form-control" id="latitude">
                </div>
            </td>
        </tr>
        <tr>
            <td class='w-40'>
                <label for="longitude" class="form-label mt-2">Longitude (decimal)</label>
            </td>
            <td class='w-10'></td>
            <td class="w-50">
                <div class="input-group">
                    <input type='text' class="form-control" id="longitude">
                </div>
            </td>
        </tr>
        <tr>
            <td class='w-40'></td><td class="w-10"></td>
            <td class="w-50"><button class='btn btn-sm btn-warning'>get coordinates from map</button></td>
        </tr>
        <tr>
            <td class='w-40'>
                <label for="altitude" class="form-label mt-2">Altitude (above sea level)</label>
            </td>
            <td class='w-10'><input id='inc_altitude' type='checkbox' class='form-check-input mt-2' checked></td>
            <td class="w-50">
                <div class="input-group">
                    <input type='text' class="form-control" id="altitude">
                    <button class='btn btn-sm btn-outline-secondary toggleAltitudeUnit' type='button'>metres</button>
                </div>
            </td>
        </tr>
        <tr>
            <td class='w-40'>
                <label for="dateStamp" class="form-label mt-2">Date Stamp</label>
            </td>
            <td class='w-10'><input id='inc_date' type='checkbox' class='form-check-input mt-2' checked></td> 
            <td class="w-50">
                <div class="input-group">
                    <input type='date' class="form-control" id="dateStamp">
                </div>
            </td>
        </tr>
        <tr>
            <td class='w-40'>
                <label for="timeStamp" class="form-label mt-2">Time Stamp</label>
            </td>
            <td class='w-10'><input id='inc_time' type='checkbox' class='form-check-input mt-2' checked></td>
            <td class="w-50">
                <div class="input-group">
                    <input type='time' class="form-control" id="timeStamp">
                </div>
            </td>
        </tr>
        </table>        
      </div>
      <hr style="margin:0">
      <div class="modal-footer">
        <button type="button" id='addGPStoOriginal' class="btn btn-success">Add to Original</button>
        <button type="button" class="btn btn-success">Add to Sidecar</button>
        <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cancel</button>
      </div>
    </div>
  </div>
</div>
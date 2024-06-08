<div class="modal fade" id="editTagsModal" tabindex="-1" aria-labelledby="editTagModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-md">
    <div class="modal-content">
      <div class="modal-header">
        <img src='../../assets/cameraLogo.png' width=15%/>
        <h3 class="modal-title" id="editTagModalLabel" style='padding-top: 10px; padding-left:20px'><b>Edit Metadata</b></h3>
        <button type="button" class="btn-close" data-bs-dismiss="modal" ahttp://127.0.0.1:8080/#ria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Add or edit certain image metadata<p>
        
        <div class="editTagBlock">
            <div class='row editTagItem input-group mb-3'>
                <label for="cameraMake" class="form-label mt-2 col-sm-12 col-md-4">Camera make</label>
                <input type='text' class="form-control me-3 col-sm-11 col-md-7" id="cameraMake">
            </div>
            <div class='row editTagItem input-group mb-3'>
                <label for="cameraModel" class="form-label mt-2 col-sm-12 col-md-4">Camera model</label>
                <input type='text' class="form-control me-3 col-sm-11 col-md-7" id="cameraModel">
            </div>
            <div class='row editTagItem input-group mb-3'>
                <label for="lensModel" class="form-label mt-2 col-sm-12 col-md-4">Lens model</label>
                <input type='text' class="form-control me-3 col-sm-11 col-md-7" id="lensModel">
            </div>       
        </div>

        <div class='row editTagBlock'>
            <div class="col-xl-4 col-lg-6">
                <label for="description" class="form-label mt-2">Description</label>
                <input type='text' class="form-control" id="description">
            </div>
            <div class="col-xl-4 col-lg-6">
                <label for="artist" class="form-label mt-2">Artist</label>
                <input type='text' class="form-control" id="artist">
            </div>
            <div class="col-xl-4 col-lg-6">
                <label for="copyright" class="form-label mt-2">Copyright</label>
                <input type='text' class="form-control" id="copyright">
            </div>
        </div>
        <div class='row editTagBlock'>
            <div class="col-xl-4 col-lg-6">
                <label for="rating" class="form-label mt-2">Rating</label>
                <input type='text' class="form-control" id="rating">
            </div>
            <div class="col-xl-4 col-lg-6">
                <label for="ratingPercent" class="form-label mt-2">Rating percent</label>
                <input type='text' class="form-control" id="ratingPercent">
            </div>
            <div class="col-xl-4 col-lg-6">
                <label for="userComment" class="form-label mt-2">Comment</label>
                <input type='text' class="form-control" id="userComment">
            </div>
        </div>
        <div class='row editTagBlock'>
            <div class="col-xl-4 col-lg-6">
                <label for="shutter" class="form-label mt-2">Shutter speed</label>
                <input type='text' class="form-control" id="shutter">
            </div>
            <div class="col-xl-4 col-lg-6">
                <label for="aperture" class="form-label mt-2">ƒ number</label>
                <input type='text' class="form-control" id="aperture">
            </div>
            <div class="col-xl-4 col-lg-6">
                <label for="ISO" class="form-label mt-2">ISO</label>
                <input type='text' class="form-control" id="ISO">
            </div>
        </div>
        
    </div>
      <hr style="margin:0">
      <div class="modal-footer">
        <button type="button" id='addNewTags' class="btn btn-success">Add to Original</button>
        <button type="button" class="btn btn-success">Add to Sidecar</button>
        <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cancel</button>
      </div>
    </div>
  </div>
</div>
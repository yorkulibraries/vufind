<div class="modal-header">
  <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
  <h4 class="modal-title">{$alertTitle|escape}</h4>
</div>
<div class="modal-body">
  <div class="alert alert-{$alertType}">
    {$alertMessage|escape}
  </div>
</div>
<div class="modal-footer">
  <button type="button" class="btn btn-primary" data-dismiss="modal">{translate text='Close'}</button>
</div>

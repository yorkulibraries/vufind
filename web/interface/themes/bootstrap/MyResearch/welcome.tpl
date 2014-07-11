<div class="alert alert-success">
  {translate text='You have successfully authenticated as'} {$user->firstname|lower|regex_replace:'/\([a-z]+\.\)/':''|ucwords|escape} {$user->lastname|lower|ucwords|escape}
</div>

<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
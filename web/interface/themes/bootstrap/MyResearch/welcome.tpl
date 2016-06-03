<p class="text-success">
  {translate text='You have successfully authenticated as'} <strong>{$user->firstname|lower|regex_replace:'/\([a-z]+\.\)/':''|ucwords|escape} {$user->lastname|lower|ucwords|escape}.</strong>
</p>

{include file="MyResearch/fines-summary.tpl"}

<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
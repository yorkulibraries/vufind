{if empty($commentList)}
  <div class="alert alert-info">{translate text='Be the first to leave a comment'}!</div>
{else}
  {foreach from=$commentList item=comment}

  <div class="panel panel-info">
    <div class="panel-heading">
      <div class="panel-title">
        {translate text='Posted by'} {$comment->fullname|lower|regex_replace:'/\s.*/':''|ucwords} {translate text='posted_on'} {$comment->created|escape}
        {if $comment->user_id == $user->id}
        <div class="btn-group pull-right">
          <a class="btn btn-danger btn-xs" data-json="{$path}/AJAX/JSON?method=deleteRecordComment&amp;recordId={$id}&amp;id={$comment->id}" href="{$path}/Record/{$id}/UserComments?delete={$comment->id}"><span class="icon-trash"></span> {translate text='Delete'}</a>
        </div>
        {/if}
      </div>
    </div>
    <div class="panel-body">
      {$comment->comment|escape}
    </div>
  </div>
  {/foreach}
{/if}

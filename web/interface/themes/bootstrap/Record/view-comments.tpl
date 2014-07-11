{if $add}
<form data-json="{$path}/AJAX/JSON?method=commentRecord" role="form" action="{$path}/Record/{$id}/UserComments" method="post">
  <input type="hidden" name="submit" value="submit" />
  <input type="hidden" name="add" value="{$add|escape}" />
  <input type="hidden" name="id" value="{$id|escape}" />

  <div class="alert-container"></div>
  <div class="form-group">
    <label class="sr-only" for="comment-form-comment">{translate text="Comment"}</label>
    <textarea class="form-control" id="comment-form-comment" name="comment" rows="4"></textarea>
  </div>
  <div class="form-group">
    <input type="submit" name="cancel" value="{translate text='Cancel'}" class="btn btn-default" data-dismiss="modal" />
    <input type="submit" name="submit" value="{translate text='Add your comment'}" class="btn btn-primary" />
  </div>
</form>
{else}
  <div id="recordCommentsList">
    {include file="Record/view-comments-list.tpl"}
  </div>
  <div class="btn-group">
    <a data-toggle="modal" data-target="#modal" class="btn btn-primary" title="{translate text='Add your comment'}" href="{$path}/Record/{$id}/UserComments?add=1"><span class="icon-comment-alt"></span> {translate text='Add your comment'}</a>
  </div>
{/if}
<h1>{translate text="User Comments"}</h1>

<table class="table table-striped table-condensed table-bordered">
<tr>
  <th>{translate text="Record ID"}</th>
  <th>{translate text="Comments"}</th>
  <th>{translate text="Posted by"}</th>
  <th>{translate text="Posted on"}</th>
  <th>{translate text="Action"}</th>
</tr>
{foreach from=$commentList item=comment}
  <tr>
    <td><a target="_blank" href="{$path}/Record/{$comment->record_id}#UserComments">{$comment->record_id}</a></td>
    <td>{$comment->comment|escape:"html"}</td>
    <td>{$comment->firstname|escape} {$comment->lastname|escape} ({$comment->cat_username|escape})</td>
    <td>{$comment->created|escape:"html"}</td>
    <td><a class="btn btn-danger btn-sm" href="{$path}/Admin/UserComments?delete={$comment->id}">{translate text="Delete"}</a></td>
  </tr>
{/foreach}
</table>  

<form data-json="{$path}/AJAX/JSON?method=feedbackSubmit" role="form" method="post" action="{$path}/Feedback/Submit">
  <div class="form-group">
    <label for="feedbackEmail">{translate text='Your email address'}</label>
    <input type="email" class="form-control" id="feedbackEmail" name="from" {if $user && $user->email}value="{$user->email}"{/if}/>
  </div>
  <div class="form-group">
    <label for="feedbackLike">{translate text='What do you like'}?</label>
    <textarea class="form-control" rows="2" id="feedbackLike" name="like"></textarea>
  </div>
  <div class="form-group">
    <label for="feedbackImprovement">{translate text='What needs to be improved'}?</label>
    <textarea class="form-control" rows="2" id="feedbackImprovement" name="improvement"></textarea>
  </div>
  <input type="submit" class="btn btn-default" data-dismiss="modal" name="cancel" value="{translate text='Cancel'}" />
  <input type="submit" class="btn btn-primary" name="submit" value="{translate text='Send'}" />
</form>

function success_msg(msg) {
  showMsg(
    '<svg style="margin-right: 5px;margin-top: -2px;" xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" width="25" height="25" x="0" y="0" viewBox="0 0 512 512" style="enable-background:new 0 0 512 512" xml:space="preserve" class=""><g><path d="M256 0C114.836 0 0 114.836 0 256s114.836 256 256 256 256-114.836 256-256S397.164 0 256 0zm129.75 201.75L247.082 340.414c-4.16 4.16-9.621 6.254-15.082 6.254s-10.922-2.094-15.082-6.254l-69.332-69.332c-8.344-8.34-8.344-21.824 0-30.164 8.34-8.344 21.82-8.344 30.164 0l54.25 54.25 123.586-123.582c8.34-8.344 21.82-8.344 30.164 0 8.34 8.34 8.34 21.82 0 30.164zm0 0" fill="#ffffff" opacity="1" data-original="#000000" class=""></path></g></svg>' +
      msg,
    "bg-primary"
  );
}

function danger_msg(msg) {
  showMsg(
    '<svg style="margin-right: 5px;margin-top: -2px;" xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" width="25" height="25" x="0" y="0" viewBox="0 0 24 24" style="enable-background:new 0 0 512 512" xml:space="preserve" class=""><g><path d="M12 1a11 11 0 1 0 11 11A11.013 11.013 0 0 0 12 1zm4.242 13.829a1 1 0 1 1-1.414 1.414L12 13.414l-2.828 2.829a1 1 0 0 1-1.414-1.414L10.586 12 7.758 9.171a1 1 0 1 1 1.414-1.414L12 10.586l2.828-2.829a1 1 0 1 1 1.414 1.414L13.414 12z" data-name="Layer 2" fill="#ffffff" opacity="1" data-original="#000000" class=""></path></g></svg>' +
      msg,
    "bg-danger"
  );
}
function warning_msg(msg) {
  showMsg(
    '<svg style="margin-right: 5px;margin-top: -2px;" xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" width="25" height="25" x="0" y="0" viewBox="0 0 24 24" style="enable-background:new 0 0 512 512" xml:space="preserve" class=""><g><g fill="#000"><path d="M12 22.75C6.07 22.75 1.25 17.93 1.25 12S6.07 1.25 12 1.25 22.75 6.07 22.75 12 17.93 22.75 12 22.75zm0-20C6.9 2.75 2.75 6.9 2.75 12S6.9 21.25 12 21.25s9.25-4.15 9.25-9.25S17.1 2.75 12 2.75z" fill="#ffffff" opacity="1" data-original="#000000"></path><path d="M12 13.75c-.41 0-.75-.34-.75-.75V8c0-.41.34-.75.75-.75s.75.34.75.75v5c0 .41-.34.75-.75.75zM12 17c-.13 0-.26-.03-.38-.08s-.23-.12-.33-.21c-.09-.1-.16-.2-.21-.33-.05-.12-.08-.25-.08-.38s.03-.26.08-.38.12-.23.21-.33c.1-.09.21-.16.33-.21a1 1 0 0 1 .76 0c.12.05.23.12.33.21.09.1.16.21.21.33s.08.25.08.38-.03.26-.08.38c-.05.13-.12.23-.21.33-.1.09-.21.16-.33.21s-.25.08-.38.08z" fill="#ffffff" opacity="1" data-original="#000000"></path></g></g></svg>' +
      msg,
    "bg-warning"
  );
}
function back() {
  window.history.back();
}

function loadFile(event, id) {
  var image = document.getElementById(id);
  image.src = URL.createObjectURL(event.target.files[0]);
}

function loadFileeditn(id) {
  jQuery("#" + id).trigger("click");
}
function showMsg(msg, des) {
  const t = document.querySelector(".toast-placement-ex");
  document.querySelector(".toast-body").innerHTML = msg;
  let o, s, c;
  var e;
  c &&
    (e = c) &&
    null !== e._element &&
    (t &&
      (t.classList.remove(o),
      DOMTokenList.prototype.remove.apply(t.classList, s)),
    e.dispose()),
    (o = des),
    // (s = "bottom-0 start-0"),
    t.classList.add(o),
    // t.classList.add(s),
    // DOMTokenList.prototype.add.apply(t.classList, s),
    (c = new bootstrap.Toast(t)).show();
}

$(document).on("keypress", ".numberonly", function (event) {
  if (
    (event.which != 46 || $(this).val().indexOf(".") != -1) &&
    (event.which < 48 || event.which > 57)
  ) {
    event.preventDefault();
  }
});

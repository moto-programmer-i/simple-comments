// console.log(objFromPHP);
/*
objFromPHP: simple-comments.php内、wp_localize_scriptの
array(
    'post_url' => （省略）,
    'nonce'    => （省略）,
)
*/
console.log(objFromPHP.nonce);

insert_comments_html();

async function insert_comments_html() {
    // console.log("comment");
    // 記事であるかチェック
    const main = document.getElementById("main");
    if(!main || document.getElementsByClassName("post").length <= 0) {
        return;
    }
    const div = document.createElement("div");
    

    // Cocoonの設定を真似てコメントを作る
    // <form action="http://www.failure4.shop/success-laugh/wp-comments-post.php" method="post" id="commentform" class="comment-form"><p class="logged-in-as">motopgi としてログインしています。<a href="http://www.failure4.shop/success-laugh/wp-admin/profile.php">プロフィールを編集します</a>。<a href="http://www.failure4.shop/success-laugh/wp-login.php?action=logout&amp;redirect_to=http%3A%2F%2Fwww.failure4.shop%2Fsuccess-laugh%2Farchives%2F1&amp;_wpnonce=d016cb6ac1">ログアウトしますか</a> ?</p><p class="comment-form-comment"><label for="comment">コメント <span class="required">※</span></label> <textarea id="comment" name="comment" cols="45" rows="8" maxlength="65525" required="required"></textarea></p><p class="form-submit"><input name="submit" type="submit" id="submit" class="submit" value="コメントを送信"> <input type="hidden" name="comment_post_ID" value="1" id="comment_post_ID">
    const form = document.createElement("form");
    form.classList.add("comment-form");
    form.method = "POST";
    form.action = objFromPHP.post_url;
    // alert(form.action);
    
    
    // <textarea id="comment" name="comment" cols="45" rows="8" maxlength="65525" required="required"></textarea>
    const textarea = document.createElement("textarea");
    textarea.name = "content";
    textarea.cols = 45;
    textarea.rows = 8;
    textarea.maxLength = 65525;
    textarea.required = true;
    form.appendChild(textarea);

    // <input name="submit" type="submit" id="submit" class="submit" value="コメントを送信"></input>
    const button = document.createElement("input");
    button.type = "submit"
    button.classList.add("submit");
    button.value = "コメントを送信";
    form.appendChild(button);

    // <input type="hidden" name="comment_parent" id="comment_parent" value="0">
    const parent = document.createElement("input");
    parent.name = "parent";
    parent.type = "hidden"
    form.appendChild(parent);

    const nonce = document.createElement("input");
    nonce.name = "nonce";
    nonce.type = "hidden"
    nonce.value = objFromPHP.nonce;
    form.appendChild(nonce);
    

    div.appendChild(form);
    main.appendChild(div);
}

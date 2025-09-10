// console.log(objFromPHP);

// objFromPHP: simple-comments.phpのwp_localize_script内参照

// console.log(objFromPHP.nonce);

// simple-comments-constants.phpと二重定義になってしまっている
// 頑張れば解決できるかもしれないが、今回は面倒なのでこれで
const POST_ID_CONTACT = 0;
const POST_ID_PRIVACY_POLICY = 1;

insertCommentsHtml();

// simple-comments-post.phpと二重定義になってしまっている
// 頑張れば解決できるかもしれないが、今回は面倒なのでこれで
function getPostId() {
    let postId = POST_ID_CONTACT;
    const url = window.location.toString();
    // http://www.failure4.shop/success-laugh/privacy-policy
    if (url.includes("privacy-policy")) {
        return POST_ID_PRIVACY_POLICY;
    }
    else {
        // http://www.failure4.shop/success-laugh/archives/1
        postId = parseInt(url.replace( /.*archives\/(\d+).*/, "$1"));
        if (isNaN(postId)) {
            alert("post_idが不正です：" + url);
        }
    }

    return postId;
}

function getComments(postId) {
    // https://ja.wordpress.org/team/handbook/plugin-development/javascript/summary/#jquery
    $.get(objFromPHP.getCommentUrl + "&post_id=" + postId, function(commentsStr) {
        comments = JSON.parse(commentsStr);
        // simple-comments.phpの $get_comments参照
        const main = document.getElementById("main");
        const commentDiv = document.createElement("div");
        commentDiv.classList.add("simple-comments");

        // コメント欄作成
        for(let comment of comments) {
            const div = document.createElement("div");

            // radio コメント内容と表示する
            const radio = document.createElement("input");
            radio.type = "radio";
            radio.name = "parent";
            radio.value = comment.id;
            div.appendChild(radio);
            const content = document.createElement("div");
            content.innerText = comment.content;
            div.appendChild(content);
            
            commentDiv.appendChild(div);
        }
        main.appendChild(commentDiv);
    });
    
}

async function insertCommentsHtml() {
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
    form.action = objFromPHP.postUrl;
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

    // URLから取得
    getComments(getPostId());
}

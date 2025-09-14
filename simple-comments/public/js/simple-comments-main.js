// console.log(objFromPHP);

// objFromPHP: simple-comments.phpのwp_localize_script内参照

// console.log(objFromPHP.nonce);

// simple-comments-constants.phpと二重定義になってしまっている
// 頑張れば解決できるかもしれないが、今回は面倒なのでこれで
const POST_ID_CONTACT = 0;
const POST_ID_PRIVACY_POLICY = 1;

const CONTENT = "content";

// IDが他と被っているので変更
const CONTENT_ID = "simple-comments-content";

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
    else if(url.includes("archives")) {
        // http://www.failure4.shop/success-laugh/archives/1
        postId = parseInt(url.replace( /.*archives\/(\d+).*/, "$1"));
        if (isNaN(postId)) {
            alert("post_idが不正です：" + url);
        }
    }

    return postId;
}

function getComments(postId, form) {
    // https://ja.wordpress.org/team/handbook/plugin-development/javascript/summary/#jquery
    $.get(objFromPHP.getCommentUrl + "&post_id=" + postId, function(commentsStr) {
        if (!commentsStr) {
            return;
        }
        comments = JSON.parse(commentsStr);
        // simple-comments.phpの $get_comments参照
        const commentDiv = document.createElement("div");
        commentDiv.classList.add("simple-comments");

        
        const idMap = new Map();
        const topLevelComments = new Array();
        for(let comment of comments) {
            comment.wrapper = document.createElement("div");
            comment.wrapper.classList.add("wrapper");

            // コメントの行作成
            comment.line = document.createElement("div");
            comment.line.classList.add("line");
            const radio = document.createElement("input");
            radio.type = "radio";
            radio.name = "parent";
            radio.value = comment.id;
            comment.line.appendChild(radio);
            const content = document.createElement("div");
            content.innerText = comment.content;
            comment.line.appendChild(content);
            comment.wrapper.appendChild(comment.line);

            // 返信の構造作成
            comment.children = new Array();
            idMap.set(comment.id, comment);
            if (comment.parent) {
                const parent = idMap.get(comment.parent);
                if (parent) {
                    parent.wrapper.appendChild(comment.wrapper);
                }
            }
            else {
                topLevelComments.push(comment);
            }
        }

        // 返信なし作成
        const noReplyLine = document.createElement("div");
        noReplyLine.classList.add("line");
        const noReply = document.createElement("input");
        noReply.type = "radio";
        noReply.name = "parent";
        noReply.value = null;
        noReplyLine.appendChild(noReply);
        const noReplyLabel = document.createElement("div");
        noReplyLabel.innerText = "返信なし（下のコメントのボタンを選択すると返信になります）";
        noReplyLine.appendChild(noReplyLabel);
        commentDiv.appendChild(noReplyLine);

        // コメント欄作成
        for(let comment of topLevelComments) {
            commentDiv.appendChild(comment.wrapper);
        }
        form.appendChild(commentDiv);
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
    textarea.id = CONTENT_ID;
    textarea.name = CONTENT;
    textarea.cols = 45;
    textarea.rows = 4;
    textarea.maxLength = 65525;
    textarea.required = true;
    form.appendChild(textarea);

    // nonceエラー時の復元
    const params = new URLSearchParams(window.location.search);
    if(params.get("restore")) {
        textarea.value = sessionStorage.getItem(CONTENT_ID);
    }

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

    // nonceエラーに備えて保存
    form.onsubmit = (event) => {
        sessionStorage.setItem(CONTENT_ID, document.getElementById(CONTENT_ID).value);
    };
    

    div.appendChild(form);
    main.appendChild(div);

    // URLから取得
    getComments(getPostId(), form);
}

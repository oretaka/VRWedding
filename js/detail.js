// ここから　変数宣言・初期化
// モーダル画面で「参加」が押されたかの情報。true:参加ボタンが押された　false:参加ボタンが押されていない
var entry = false;
// ここまで　変数宣言・初期化

// 引数に指定したアドレス(VRChatのルーム)へ遷移させる関数。
function entryRoom(address)
{
	location.href = address;
}

function enableEntry()
{
	entry = true;
}

// 結婚式参加のモーダルウィンドウを開く関数。
$( function() {
	$('#attend').click( function () {
		$('#sampleModal').modal({
			backdrop:"static",
		});
	});
	// 「参加する」ボタンを押してモーダルを閉じた時に実行するメソッド。
	$('#sampleModal').on('hidden.bs.modal', function () {
		if(entry)
		{
			entry = false;
			// フォームのデータを、phpにpostで送信。
			$('#attendForm').submit();

		}
	});
});
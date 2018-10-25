<?php
// データベースへのレコード追加・検索・更新を総合して行う、DAOオブジェクト。
class Dao
{
	// フィールド宣言　ここから
	// DBの接続を管理するPDOオブジェクト。
	private $pdo;
	// DBの操作で使用するSQL文。
	private $sql;
	// フィールド宣言　ここまで

	// コンストラクタ
	public function __construct(){
		$this->pdo = null;
		$this->sql = "";
		$this->stmt = null;
	}

	// DBの検索処理を行うメソッド。
	// 引数:
	// $tableName: selectの対象となるテーブル名を指定してください。必須項目です。
	// $colName[]:　selectの検索対象とするカラム名を、配列で指定してください。なお、nullを指定した場合、*(テーブルの全てのカラムが対象)となります。
	// $whereList[]: selectで条件指定がある場合、条件文をまとめた配列をセットしてください。nullを指定した場合、where文による指定はないものとします。
	// $whereListValue[]: selectで条件指定する場合の、?に対応する値を配列で指定。where文なしの場合、nullをセット。
	// $andor: where文を複数指定する場合の検索条件。ANDまたはORをセットしてください。where文が2件以上ない場合はnullをセット。
	// $options: where文の後に付ける、DB抽出のオプション設定をセットしてください(例:ORDER BYなど)。オプションを指定しない場合、nullをセット。
	// 戻り値:
	// $result[]: selectによる抽出結果を、連想配列形式で返します。配列の要素名は、結果テーブルのカラム名と連動します。
	public function select($tableName,$colName,$whereList = null,$whereListValue = null,$andor = null,$options = null)
	{
		// ここから　変数宣言
		// DBの取得結果を、配列で返す変数。
		$result = array();
		// プリペアードステートメントを使う必要がある場合、trueとなる。使う必要がなければfalse。
		$needPrepared = false;
		// ここまで　変数宣言

		// 引数にセットされた情報を基に、SQL文を生成。必要に応じてプリペアードステートメントを使用する。
		$this->sql = "SELECT ";
		if($colName == null)
		{
			$this->sql .= "* ";
		}
		else
		{
			$this->sql .= $this->generateByColName($colName);
		}
		$this->sql .= "FROM " . $tableName;
		// where文
		if($whereList != null)
		{
			// プリペアードステートメントが必要。
			$needPrepared = true;
			$this->sql .= $this->generateByWhere($whereList,$andor);
		}
		// オプション文。
		if($options != null)
		{
			$this->sql .= " " . $options;
		}

		// PDOの初期化。必要に応じプリペアードステートメントに対応させる。
		$this->startPDO($needPrepared);
		// SQL文を実行し、取得結果を返す。
		$result = $this->doSQL($this->sql,true,$needPrepared,$whereListValue);
		// PDOの通信切断。
		$this->stopPDO();


		return $result;
	}

	// DBの登録処理を行うメソッド。
	// 引数:
	// $tableName: 必須。insertの対象となるテーブル名を指定してください。
	// $colName[]:　必須。insertの登録対象とするカラム名を、配列で指定してください。
	// $insertListValue[]: 必須。insertで登録する場合の、各カラムに格納するデータ一覧。?に入る値です。テーブルの左のカラム名から順に指定してください。
	// 戻り値:
	// $result: DBへの登録が成功した場合にtrue、失敗した場合にfalseを返します。
	public function insert($tableName,$colName,$insertListValue = null)
	{
		// ここから　変数宣言
		// DBの登録が成功したらtrue、失敗したらfalseを返す変数。
		$result = true;
		// DB登録処理後の結果を格納する変数。通常はnull、エラーが発生した場合、"エラー"が入る。
		$insres = null;
		// プリペアードステートメントを使う必要がある場合、trueとなる。使う必要がなければfalse。
		$needPrepared = true;
		// ここまで　変数宣言

		// 引数にセットされた情報を基に、SQL文を生成。必要に応じてプリペアードステートメントを使用する。
		$this->sql = "INSERT INTO ";
		// テーブル名を追加。
		$this->sql .= $tableName . "(";
		$this->sql .= $this->generateByColName($colName) . ") ";
		$this->sql .= "VALUES(";

		// insertListValueの要素数分、繰り返す。
		for($i=0;$i<count($insertListValue);$i++)
		{
			$this->sql .= "?";
			if( $i != count($insertListValue) - 1)
			{
				$this->sql .= ",";
			}
		}
		$this->sql .= ")";
		
		// PDOの初期化。必要に応じプリペアードステートメントに対応させる。
		$this->startPDO($needPrepared);
		// SQL文を実行し、取得結果を返す。
		$insres = $this->doSQL($this->sql,false,$needPrepared,$insertListValue);
		// PDOの通信切断。
		$this->stopPDO();

		// この時点でDB登録の際にエラーが発生した場合は、resultをfalseとする。
		if($insres != null)
		{
			$result = false;
		}

		return $result;
	}

	// 		↓privateメソッド

	// SQL文生成メソッド。カラム名の連結を行う。
	// 引数
	// $colName[]: カラム名の配列。
	// 戻り値
	// $ret: カラム名の連結後の文字列を返します。
	private function generateByColName($colName)
	{
		$ret = "";
		foreach ($colName as $val) {
			$ret .= $val;
			if( !($val === end($colName)) )
			{
				$ret .= ",";
			}
			$ret .= " ";
		}
		return $ret;
	}

	// SQL文生成メソッド。複数のwhere文の連結を行う。
	// 引数
	// $whereList[]: where文の配列。
	// $andor: where文が複数ある場合の、ORやANDなどの条件指定。
	// 戻り値
	// $ret: where文の連結後の文字列を返します。
	private function generateByWhere($whereList,$andor)
	{
		$ret = " WHERE ";
		foreach ($whereList as $val) {
			$ret .= $val;
			if( !($val === end($whereList)) )
			{
				$ret .= " " . $andor . " ";
			}
		}

		return $ret;
	}

	// SQL文実行メソッド。引数に入れられたSQL文を実行し、結果を返すメソッドです。
	// 引数
	// $sql:実行対象となるsql文。
	// $needResult: SQL文を実行した後の結果が欲しい場合はtrue、実行で終わる場合はfalse。
	// $np: プリペアードステートメントの使用の有無。true:使用　false:不使用
	// $wlv[]: (プリペアードステートメント使用の場合)?に対応する値を格納した配列。使用しない場合はnull。
	// 戻り値
	// $ret: DBからの取得結果を格納した連想配列。ただし、データベース処理で何らかの例外が発生した場合、"エラー"の文字列を返します。
	private function doSQL($sql,$needResult,$np,$wlv)
	{
		// 変数宣言　ここから
		// DBからの取得結果を格納する連想配列。
		// 【例】$ret[0]->["name"]="小川" ["address"]="東京都"
		$ret = null;
		// DBのステートメントオブジェクト。
		$stmt = null;
		// 変数宣言　ここまで

		// 例外処理
		try
		{
			// プリペアードステートメントの必要有無に応じて分岐。
			if($np)
			{
				// 必要
				// SQL発行
				$stmt = $this->pdo->prepare($sql);

				// 値の設定
				for($i=0;$i<count($wlv);$i++)
				{
					$stmt->bindValue($i + 1,$wlv[$i]);
				}
				// SQL実行
				$stmt->execute();
			}
			else
			{
				// 不要
				$stmt =  $this->pdo->query( $sql );
			}
			
			// SQLの実行結果が必要な場合、結果データの存在する行数分ぶん回す。
			if($needResult)
			{
				$ret = array();
				while( $row = $stmt->fetch(PDO::FETCH_ASSOC) )
				{
					array_push($ret, $row);
					// print_r($row);
				}
			}
		}
		catch(Exception $e)
		{
			$ret = "エラー";
			// echo $e->getMessage();
			// echo $sql;
			// exit;
		}

		return $ret;
	}

	// PDOの初期化を行うメソッド。
	// 引数
	// $prepared:プリペアードステートメントを使う場合はtrue、使わない場合はfalseをセット。
	private function startPDO($prepared)
	{
		// 接続文字列
		// DSN(Data Source Name)
		$dsn = "mysql:host=localhost;dbname=gp41;charset=utf8";
		// DBユーザー/PW
		$db_user = "root";
		$db_password="";

		// PDOオブジェクトを初期化する。
		$this->pdo = new PDO($dsn,$db_user,$db_password);

		// 動作モードの変更
		$this->pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

		// プリペアードステートメントを使う場合、セットアトリビュートをもう1回実行する。
		if( $prepared == true )
		{
			$this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
		}
	}

	// PDOの通信切断を行うメソッド。
	private function stopPDO()
	{
		$this->pdo = null;
	}
}
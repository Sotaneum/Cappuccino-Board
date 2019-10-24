<!doctype html>
<?php 
/*
	

이 부분은 GET 방식의 접근일 때 현재 게시판의 상태를 표시하는 정보가 입력되어 있습니다.

*/

date_default_timezone_set('Asia/Seoul');
	function Install($host,$user,$pass,$db){
		$link=mysqli_connect($host,$user,$pass);
		if ($link->connect_error) {
				 die("Connection failed: " . $link->connect_error);
		} 
		echo "\n ============:".$db;
		$sql = "create database ".$db;
		if ($link->query($sql) === TRUE) {
			echo "Database created successfully";
		} else {
			echo "Error creating database: " . $link->error;
		}
		$link->close();
		$link=mysqli_connect($host,$user,$pass,$db);
		$sql ="create table post (
		no int, 
		board_id int, 
		title varchar(120),
		name varchar(12),
		member_id int,
		content text,
		comment_id int, 
		date_update timestamp,
		date_write date,
		hit int,
		multi varchar(10),
		file varchar(250),
		passowrd varchar(300),
		primary key (no,board_id))";

		if ($link->query($sql) === TRUE) {
			echo "Table MyGuests created successfully";
		} else {
			echo "Error creating table: " . $link->error;
		}
	

		$sql ="create table comment (
		no int,
		board_id int,
		post_id int,
		name varchar(12),
		date timestamp,
		comment text,
		password varchar(300),
		primary key(no,board_id,post_id))";

		if ($link->query($sql) === TRUE) {
			echo "Table MyGuests created successfully";
		} else {
			echo "Error creating table: " . $link->error;
		}
		$mydir = "upload_file"; 
		 if(@mkdir($mydir, 0777)) { 
			if(is_dir($mydir)) { 
				@chmod($mydir, 0777); 
				echo "${mydir} 디렉토리를 생성하였습니다."; 
			} 
		 } 
		 else { 
			echo "${mydir} 디렉토리를 생성하지 못했습니다."; 
		 } 
	}
	
	$install=isset($_GET['install'])?$_GET['install']:"100";
	if($install==10)
	{
		?>
		<div>
			<table>
				<tr>
					<td>
						<form action="list.php?install=1000" method="post">
							<input name="host" type=text placeholder="호스트">
							<input name="user" type=text placeholder="유저 이름">
							<input name="pass" type=password placeholder="비밀번호">
							<input name="db" type=text placeholder="DB 이름">
							<input type=submit value="저장">
						</form>
					</td>
				</tr>
			</table>
		</div>
		
		<?php
			return;
	}else if($install==1000){
		$host=isset($_POST['host'])?$_POST['host']:"";
		$user=isset($_POST['user'])?$_POST['user']:"";
		$pass=isset($_POST['pass'])?$_POST['pass']:"";
		$db=isset($_POST['db'])?$_POST['db']:"";

		$myfile = fopen("libd.php", "w") or die("Unable to open file!");
		$txt = "<?php \n";
		fwrite($myfile, $txt);
		$txt = "$"."host='$host';\n";
		fwrite($myfile, $txt);
		$txt = "$"."user='$user';\n";
		fwrite($myfile, $txt);
		$txt = "$"."pass='$pass';\n";
		fwrite($myfile, $txt);
		$txt = "$"."db='$db';";
		fwrite($myfile, $txt);
		$txt = "?>";
		fwrite($myfile, $txt);
		fclose($myfile);
		Install($host,$user,$pass,$db);
		echo "<script>location.replace('./list.php?board=1');</script>";
	}
	else
	{
		$filepath = "./libd.php";

		if(!file_exists($filepath))
		{
				?><script>location.replace('./list.php?install=10');</script><?php
		}
	}
	
	//데이터베이스 정보를 가져온다.
	include_once("./libd.php");	
	
	$link = mysqli_connect($host,$user,$pass,$db);

	//게시판의 아이디를 가져온다.
	$board = isset($_GET['board'])?$_GET['board']:"";
	//게시판의 아이디가 입려되어 있지 않다면 접속 불가
	if($board=="")
	{
	  echo "잘못된 접근이거나 존재하지 않은 페이지 입니다.";
	  return;
	}
	//최대 표시 개수를 가져온다.
	$maxpage = isset($_GET['maxpage'])?$_GET['maxpage']:"";
	//최대 표시 개수가 없다면 10으로 초기화 한다.
	if($maxpage=="")	
	{
	  $maxpage=10;
	}
	//현재 페이지를 가져온다.
	$page = isset($_GET['page'])?$_GET['page']:"";
	//현재 페이지의 정보가 없다면 1으로 초기화한다.
	if($page=="")
	{
		$page=1;	//page 정보가 없을 경우 1로 초기화한다.
	}
	//검색 정보를 가져온다.
	$searchWord=isset($_GET['searchWord'])?$_GET['searchWord']:"";
	//현재 포스트 정보를 가져온다.
	$post_number = isset($_GET['post_number'])?$_GET['post_number']:"";
	//현재 업로드 여부를 가져온다.
	$comment_upload = isset($_GET['comment_upload'])?$_GET['comment_upload']:"";
	//댓글을 삭제 여부를 가져온다.
	$comment_delete = isset($_GET['comment_delete'])?$_GET['comment_delete']:"";
	//댓글 삭제 여부를 가져온다.
	$post_delete = isset($_GET['post_delete'])?$_GET['post_delete']:"";
	//게시글 쓰기 여부를 가져온다.
	$post_write = isset($_GET['post_write'])?$_GET['post_write']:"";
	//게시글 삽입 여부를 가져온다.
	$post_insert = isset($_GET['post_insert'])?$_GET['post_insert']:"";

	//모드에 따라 쿼리 문장이 달라진다.
	if($comment_upload=="true")
	{
		/**
			댓글 업로드
		*/

		//디비를 열고
		$link=mysqli_connect($host,$user,$pass,$db);
		//현재 시간을 가져오고
		$datum = new DateTime();
		$startTime = $datum->format('Y-m-d H:i:s');
		//POST로 추가적인 정보를 더 가져온다.
		$name=isset($_POST['comment_name'])?$_POST['comment_name']:"";
		$text=isset($_POST['comment_text'])?$_POST['comment_text']:"";
		$post_id=isset($_POST['comment_post_id'])?$_POST['comment_post_id']:"";
		$no=isset($_POST['comment_no'])?$_POST['comment_no']:"";
		$password=isset($_POST['comment_password'])?$_POST['comment_password']:"";

		//값이 비어있으면 뒤로 이동한다.
		if($name=="" || $text=="" || $password=="")
		{
			echo "<script>history.back();</script>";
		}

		//no값을 내림차순으로 해서 1번이 가장 큰 숫자가 되도록한다.
		$sql ="select no from comment where board_id='$board' and post_id='$post_id' order by no Desc";
		$insert = $link->query($sql);
		$no=1;
		//값이 없으면 no를 1로 설정한다.
		if($insert!=null)
		{
			$no=$insert->fetch_assoc()['no']+1;
		}
		//비밀번호를 암호화 한다.
		$password = password_hash($password, PASSWORD_DEFAULT); 
		$sql="insert into comment value('$no','$board','$post_id','$name','$startTime','$text','$password')";
		$upload = $link->query($sql);
		echo "<script>history.back();</script>";
	}
	else if($comment_delete=="true")
	{
		/**
			댓글 삭제
		*/


		$link=mysqli_connect($host,$user,$pass,$db);
		$post_id=isset($_POST['comment_post_id'])?$_POST['comment_post_id']:"";
		$no=isset($_POST['comment_no'])?$_POST['comment_no']:"";
		$password=isset($_POST['comment_password'])?$_POST['comment_password']:"";
		if($post_id=="" || $no=="" || $password=="")
		{
			echo "<script>history.back();</script>";
		}
		//비밀번호 확인을 위해 쿼리한다.
		$sql="select password from comment where post_id='$post_id' and board_id='$board' and no='$no'";
		$hash=$link->query($sql)->fetch_assoc();
		//echo "password : ".$hash['password'];
		//비밀번호가 맞으면 삭제
		if (password_verify($password, $hash['password'])) { 
			$sql="delete from comment where no='$no' and post_id='$post_id' and board_id='$board'";
			$delete = $link->query($sql);
		}
		echo "<script>history.back();</script>";
	}
	else if($post_delete=="true")
	{
		/**
			게시글 삭제
		*/


		$link=mysqli_connect($host,$user,$pass,$db);
		$post_no=isset($_POST['post_no'])?$_POST['post_no']:"";
		$post_password=isset($_POST['post_password'])?$_POST['post_password']:"";
		if($post_no=="" || $post_password=="")
		{
			echo "<script>history.back();</script>";
		}
		$sql="select * from post where board_id='$board' and no='$post_no'";
		$hash=$link->query($sql)->fetch_assoc();
		//echo "password : ".$hash['password'];
		//echo"1".$post_password.$hash['password'];
		if (password_verify($post_password, $hash['password'])) { 
			echo"2";
			$sql="select file from post where board_id='$board' and no='$post_no'";
			$file=$link->query($sql)->fetch_assoc();

			//업로드된 파일이 있다면 삭제한다.
			if($file['file']!="")
			{
				unlink($file['file']);
			}
			$sql="delete from post where board_id='$board' and no='$post_no'";
			$delete = $link->query($sql);

			//같이 연동되어 있던 댓글 Table도 정리한다.
			$sql="delete from comment where board_id='$board' and post_id='$post_no'";
			$delete = $link->query($sql);
		}
		
		echo "<script>history.back();</script>";
	}
	else if($post_insert=="true")
	{
		$link=mysqli_connect($host,$user,$pass,$db);
		$sql ="select no from post where board_id='$board' order by no Desc";
		$insert = $link->query($sql);
		$datum;
		$startTime;
		$dirAdd_;
		try
		{
			$datum = new DateTime();
			$startTime = $datum->format('Y-m-d H:i:s');
			$dirAdd_ = $datum->format('Y_m_d_H_i_s');
		}catch(Exception $e){
			$startTime ='1970-01-01 00:00:01';
			$dirAdd_ = '1970_01_01_00_00_01';
		}
		$no=1;
		if($insert)
		{
			$no=$insert->fetch_assoc()['no']+1;
			
		}
		
		//파일을 업로드 한다.
		$target_dir="upload_file/".$board.$no.$dirAdd_;
		//$file=isset($_POST['file'])?$_POST['file']:"";
		$extension;
		$target;
		//업로드를 한 파일이 있을 경우
		if(is_uploaded_file($_FILES["file"]["tmp_name"]))
		{
			//타입을 가져온다.
			$extension=$_FILES["file"]["type"];

			//만약 입력된 타입일 경우에만 업로드가 완료 된다.
			if(strcmp($extension,"image/jpeg")==0 || strcmp($extension,"image/png")==0)
			{
				$dest=$target_dir.$_FILES["file"]["name"];
				//임시 파일을 복사한다.
				if(!move_uploaded_file($_FILES["file"]["tmp_name"],$dest))
				{
					die("파일을 지정한 디렉토리에 저장하는데 실패했습니다.");
					$extension="";
					$target="";
				}
				else
				{
					$target=$target_dir.$_FILES["file"]["name"];
				}
			}
		}
		else
		{
			$extension="";
			$target="";
		}
		
		$name=isset($_POST['name'])?$_POST['name']:"";
		$title=isset($_POST['title'])?$_POST['title']:"";
		$text=isset($_POST['text'])?$_POST['text']:"";
		$password=isset($_POST['password'])?$_POST['password']:"";
		if($name=="" || $title=="" || $password=="")
		{
			echo "<script>history.back();</script>";
		}
		//echo $no;
		$password = password_hash($password, PASSWORD_DEFAULT); 
		$sql="insert into post value('$no','$board','$title','$name','0','$text','$no','$startTime','$startTime','0','$extension','$target','$password')";
		$upload = $link->query($sql);
		//post+ 반환시 만료되는 문제가 있어 다시 새로고침
		$show=getReturn($board,$page,$maxpage,$searchWord);
		echo "<script>location.href('$show');</script>";
	}
 ?>
 <!--


이 부분부터 HTML으로 들어갑니다. 기본적인 틀이 잡혀 있습니다.


 -->
<html>
	<head>
		<title> ICT BOARD </title>
		<!-- JSON을 이용하는 기능이 있어 해당 관련 스크립트를 JSON으로 부터 가져옵니다. -->
		<script src="http://code.jquery.com/jquery-latest.js"></script> 
		<!-- Style 파일을 따로 관리하기 때문에 가져옵니다. -->
		<link rel="stylesheet" type="text/css" href="style.css">
	</head>
	<body>
		<!-- 리스트 부분에 해당합니다. -->
		<div id="bbs">
			<table>
				<!-- 게시판 목록 머리말 -->
				<tr>
					<th class="NO">NO</th>
					<th class="TITLE">TITLE</th>
					<th class="DATE">DATE</th>
					<th class="HIT">HIT/MULTI</th>
				</tr>
					<!-- 게시글 -->
				<?php
				/*
				
					서버에서 데이터를 가져와서 리스트를 뿌려주는 부분입니다.

				*/
				//DB에 접속합니다.
				$link=mysqli_connect($host,$user,$pass,$db);
				//DB에 오류가 발생시 오류를 표시합니다.
				if(mysqli_connect_errno()){
					die('Connect Error: '.mysqli_connect_error());
				}
				//시작 포스트를 결정합니다. 이는 각각의 페이지마다 처음에 보여지는 게시글을 말합니다. ( 기준은 DBMS에서 정합니다. )
				$spage=($page-1)*$maxpage;
				//쿼리를 통해 게시판에 대한 정보를 가져옵니다.
				$sql="select no, title,date_write,hit,multi,file,comment_id from post where board_id=".$board." and title like '%".$searchWord."%' order by no desc LIMIT ".$spage.",".$maxpage;
				$post_all = $link->query($sql);
				
				//반복을 통해 화면에 리스트를 뿌려줍니다.
				while($post_list_array = $post_all->fetch_assoc())
				{
					//DB에서 댓글 개수를 가져옵니다. 
					$comment_size=$link->query("select count(*) as count from comment where board_id=".$board." and post_id=".$post_list_array['no'])->fetch_object()->count;
					?>
							<!--


								게시판의 정보를 뿌려주는 부분입니다.

							
							-->
							<tr>
								<td class="NO"><?php echo $post_list_array['no']; ?></td>
								<td class="TITLE">
									<!-- 아래 onclick으로 화면에 레이어를 띄웁니다. -->
									<?php  $show=getReturn($board,$page,$maxpage,$searchWord)."&post_number=".$post_list_array['no']; ?>
									<a href="<?php echo $show; ?>"><?php echo $post_list_array['title']; 
									if($post_list_array['file']!="")
									{
										?>
												<img src="./img/file.png"></img>
										<?php
									}
									echo "<span class='bbs-strong'>[".$comment_size."]</span>";
									?>
									</a>
								</td>
								<td class="DATE"><?php echo $post_list_array['date_write']; ?></td>
								<?php  if($post_list_array['multi']!=""){ ?>
								<td class="HIT"><?php echo $post_list_array['hit']; ?>/<?php echo $post_list_array['multi']; ?></td>
								<?php }else{?>
								<td class="HIT"><?php echo $post_list_array['hit']; ?>/없음</td>
								<?php } ?>
							</tr>
					<?php
				}
				?>
			<!-- 게시글 끝 -->
			</table>
			<!--

			
				하단 부분에 페이지를 이동 할 수 있도록 보여주는 부분입니다.


			-->
			<div id="paging">
				  <?php
					//DB에서 포스트의 총 개수를 가져옵니다.
					$post_size = $link->query("select count(*) as count from post where board_id=".$board)->fetch_object()->count;
					//카운트에 최대 표시 가능한 개수를 나누어 표시할 숫자를 파악합니다.
					$count=$post_size/$maxpage;
					if($count<1)
					{
						$count=1;
					}
					//올림을 통해 최대 표시 개수가 되지 않아도 출력이 되도록 합니다.
					$count=ceil($count);
					//반복을 통해 숫자를 출력합니다.
					for($i=1;$i<=$count;$i+=1)
					{
						/*

						
							페이지가 넘어갈때마다 기존의 정보는 보존시키기 위해 여러 정보를 담는 부분입니다.
						

						*/
						if($i==$page){echo"<span class='NUMBERCHECK'>";}
						//현재 페이지에 입력된 정보에 따라 이동하는 정보가 다르게 표시
						$data=getReturn($board,$i,$maxpage,$searchWord);
						?>
						<a href= "<?php echo $data?>">[<?php echo $i?>]</a>
						<?php
						if($i==$page){echo"</span>";}
					}
				  ?>
			</div>
			<!--


				하단부분의 컨트롤 입니다.


			-->
			<div id="list-menu">
				<?php 
				
				if($post_write==true)
				{
				?>
					<div id="post_write" style="display:none;">
					<?php 
					
					$sent=getReturn($board,$page,$maxpage,$searchWord)."&post_insert=true";
					?>
						<form action="<?php echo $sent; ?>" method="post" ENCTYPE="multipart/form-data">
							<table>
								<tr>
									<td align=center>
										<input type=text name="name" placeholder="이름" size=12>
										<input type=text name="title" placeholder="제목" size=60>
									</td>
								</tr>
								<tr>
									<td align=center>
										<textarea  type=text name="text" cols="60" rows="5" placeholder="내용" size=12></textarea>
									</td>
								</tr>
								<tr>
									<td align=center>
										
										<input name='file' type="file" value="첨부파일">
										<input name="password" type="password" placeholder="비밀번호">
										<input type="submit" value="저장">
										<input onclick="history.back();" type="button" value="취소">
									</td>
								</tr>
							</table>
						</form>
					</div>
				<?php 
					echo "<script>$('#post_write' ).slideUp( 300 ).delay( 200 ).fadeIn( 400 );</script>";
				}
				else
				{
					$sent=getReturn($board,$page,$maxpage,$searchWord)."&post_write=true"; ?>
					<form action="<?php echo $sent; ?>" method="post">
					<input type="submit" value="새 글쓰기" />
					</form>

					<?php
				}
				?>
				<!--


					검색을 할때도 기존에 입력되어있는 값들도 함께 보냅니다. ( 기존의 정보에 따라 검색 결과가 달리지기 때문입니다. )


				-->
				<div id="search">
					<form action="list.php" method="get">
						<p style="margin: 0;padding: 0;" >
							<input type="hidden" name="board" value=<?php echo $board?> />
							<input type="hidden" name="page" value=<?php echo $page?> /> 
							<input type="hidden" name="maxpage" value=<?php echo $maxpage?> />
							<input type="text" name="searchWord" size="15" maxlength="30" />
							<input type="submit" value="검색" />
						</p>
					</form>
				</div>
			</div>
		</div>		
		<!--
		
		
			포스트 화면에 출력 부분

		
		-->
		<div class="background" id="post_back"><img src="./img/exit.png" width="128px" height="auto"></div>
		<div class="layout" id="post">
			<?php 
			//넘어온 값이 있을 경우 화면에 출력
			if($post_number!="")
			{
				$sql="update post set hit = hit+1 where board_id=".$board." and no=".$post_number;
				$link->query($sql);
				$sql="select * from post where board_id=".$board." and no=".$post_number;
				$post = $link->query($sql)->fetch_object();
				
				?>
			<div class="frame" id="post_frame">
				<div class="multi">
					<table>
						<tr>
							<th width="100px" nowrap><?php echo $post->date_write; ?></th>
							<th style="white-space:nowrap; overflow-x:auto;"><?php echo $post->title;?></th>
							<th width="20px" nowrap><?php echo $post->hit; ?></th>
							<th width="180px" nowrap><?php echo $post->date_update; ?></th>
						</tr>
						<tr>
							<td class="multi_real" colspan=4 align="center">
							<?php if($post->file == ""){ ?>
								<img src="./img/temp.jpg" width=100% height=100%>
							<?php }else{?>
								<img src="<?php echo $post->file; ?>" width=100% height=100%>
							<?php }?>
							</td>
						</tr>
						<tr>
							<th colspan=4>
								<?php $sent=getReturn($board,$page,$maxpage,$searchWord)."&post_delete=true"; ?>
								<form action="<?php echo $sent;?>" method="post">
								<table><tr><td>글쓴이:<?php echo $post->name; ?></td> <td> <input name="post_password" type=password placeholder="비밀번호" size=15> <input type="submit" value="삭제"  height=10px></td></tr></table>
								<input name="post_no" type=hidden value="<?php echo $post->no ?>">
								</form>
							</th>
						</tr>
						<tr>
							<td class ="content" colspan=4 style="white-space:nowrap; overflow-x:auto;">
								<?php echo $post->content; ?>
							</td>
						</tr>
					</table>
				</div>
				<!--
				

					댓글 부분

				
				-->
				<div class="comment">
				<?php 
					$sql="select * from comment where board_id=".$board." and post_id=".$post->no;
					$comment = $link->query($sql);
					while($comment_list_array = $comment->fetch_assoc())
					{
				?>
					<table>
						<tr>
							<td style="width: 70px;"><?php echo $comment_list_array['name'];?></td>
							<td style="white-space:nowrap; overflow-x:auto;"><?php echo $comment_list_array['comment'];?></td>
							<td style="width: 140px;"><?php echo $comment_list_array['date'];?></td>
						</tr>
						<tr>
							<td colspan=3 align="right">
							<?php
					
								$sent=getReturn($board,$page,$maxpage,$searchWord)."&comment_delete=true";
				?>
								<form action="<?php echo $sent; ?>" method="post">
									<input name="comment_password" type=password placeholder="비밀번호" size=15>
									<input type="submit" height=10px value="삭제">
									<input name="comment_no" type=hidden value="<?php echo $comment_list_array['no']?>">
									<input name="comment_post_id" type=hidden value="<?php echo $post->no;?>">
								</form>
							</td>
						</tr>
					</table>
					<?php
					}	
					$sent=getReturn($board,$page,$maxpage,$searchWord)."&comment_upload=true";
				?>
					<form action="<?php echo $sent ?>" method="post">
						<table>
							<tr>
								<td width=120px><input name="comment_name" type=text placeholder="이름" size=12></td>
								<td width=220px><input name="comment_text" type=text placeholder="내용" size=25></td>
								<td width=150px><input name="comment_password" type=password placeholder="비밀번호" size=15></td>
								<td width=50px align="right"><input type="submit" value="작성"></td>
							</tr>
						</table>
						<input name="comment_no" type=hidden value="<?php echo mysqli_num_rows($comment);?>">
						<input name="comment_post_id" type=hidden value="<?php echo $post->no;?>">
					</form>
				</div>
			</div>
			<?php
				}
				?>
		</div>
	</body>
<script>
	//화면에 포스트 띄우기
	function showPost(){
		$("#post_back").show("slow");
		$("#post_frame" ).slideUp( 300 ).delay( 800 ).fadeIn( 400 );
		
	}
	//배경 클릭시 닫기
	$(".background").click(function(){
		$("#post_back").hide("slow");
		$("#post_frame" ).slideUp( 300 ).delay( 800 ).fadeOut( 400 );
	});
</script>
<?php 
if($post_number!="")
{
	//스크립트를 활성화한다.
	echo"<script>showPost();</script>";
}
//기본적인 정보를 보낼때 필요한 데이터를 생성
function getReturn($board,$i,$maxpage,$searchWord){
		$data="list.php?board=".$board."&page=".$i;
		//만약에 최대 페이지가 지정되어 있다면? 같이 값을 보냄
		if($maxpage!=10)
		{
			$data=$data."&maxpage=".$maxpage;
		}
		//만약 검색 단어가 존재한다면? 그것도 같이 보냄
		if($searchWord!="")
		{
			$data=$data."&searchWord=".$searchWord;
		}

		return $data;
}
	?>
</html>
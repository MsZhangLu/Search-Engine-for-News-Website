<?php

// make sure browsers see this page as utf-8 encoded HTML 编码
header('Content-Type: text/html; charset=utf-8');
ini_set('memory_limit', '-1');
include 'SpellCorrector.php';

function getSnippet($id, $query) {
  // open html file and fetch content
  $PRE = '/Users/vickie/Documents/BigData/solr-7.1.0';
  $PRE_N = '/Users/vickie/Sites';
  $path = str_replace($PRE, $PRE_N, $id);

  $content = file_get_contents($path);
  $content = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', "", $content);
  $content = preg_replace('/<header\b[^>]*>(.*?)<\/header>/is', "", $content);
  $content = preg_replace('/<footer\b[^>]*>(.*?)<\/footer>/is', "", $content);
  $content = preg_replace('/<aside\b[^>]*>(.*?)<\/aside>/is', "", $content);
  $content = preg_replace('/<i\b[^>]*>(.*?)<\/i>/is', "", $content);
  $content = preg_replace('/<nav\b[^>]*>(.*?)<\/nav>/is', "", $content);
  $content = preg_replace('/<a\b[^>]*>(.*?)<\/a>/is', "", $content);
  $content = str_replace('&lsquo;', '', $content);
  $content = str_replace('&ldquo;', '', $content);
  $content = str_replace('&rsquo;', '', $content);
  $content = str_replace('&rdquo;', '', $content);
  $content = str_replace('&nbsp;', ' ', $content);
  $content = str_replace('&mdash;', ' ', $content);
  $content = str_replace('&#8220;', ' ', $content);
  $content = str_replace('&ndash;', ' ', $content);
  $content = str_replace('&#8221;', ' ', $content);
  $content = str_replace('&#8217;', ' ', $content);
  $content = str_replace('The Boston Globe', 'The Boston Globe.', $content);

  $content = strip_tags($content, '<p><h1>');
  $content = strip_tags($content);
  $content = str_replace('-', '', $content);

  $sentences = preg_split('~(?<=[.?!;])\s+(?=\p{Lu})~',$content);

  // var_dump($sentences);
  // process query
  $terms = explode( ' ', $query );
  $flag = False;
  if(sizeof($terms) == 1 && $flag == False) { // single term query
    foreach($sentences as $str) {
      if (stripos(strtolower($str), strtolower($terms[0]), 0) !== false) {
        $snippet = substr($str, 0, 160);
        $flag = True;
        break;
      }
    }
  }
  if($flag == False) { // multiple terms query, all terms together
    foreach($sentences as $str) {
      if (strpos(strtolower($str), strtolower(rtrim($query))) !== false) {
        $snippet = substr($str, 0, 160);
        $flag = True;
        break;
      }
    }
  }
  if($flag == False) { // multiple terms query, all terms included
    $term_len = sizeof($terms);
    foreach($sentences as $str) {
      $count = 0;
      foreach(array_map('strtolower', $terms) as $term) {
        if (stripos(strtolower($str), $term, 0) !== false) {
          $count += 1;
        }
        if($count == $term_len) {
          $snippet = substr($str, 0, 160);
          $flag = True;
          break 2;
        }
      }
    }
  }
  if ($flag == False) {
    foreach($sentences as $str) {
      foreach(array_map('strtolower', $terms) as $term) {
        if (stripos(strtolower($str), $term, 0) !== false) {
          $snippet = substr($str, 0, 160);
          $flag = True;
          break 2;
        }
      }
    }
  }
  return $snippet;
}

// 从本地文件取回url,存入$url_array
$url_array = array();
$file = file_get_contents("Boston Global Map.csv");
$Data = str_getcsv($file, "\n"); //parse the rows
foreach ($Data as $Row){
  $Row = str_getcsv($Row, ",");
  $url_array[$Row[0]] = $Row[1];
}

$limit = 10;
$retrieve_total = 10;
$query = isset($_REQUEST['q']) ? $_REQUEST['q'] : false;
$method = isset($_REQUEST['method']) ? $_REQUEST['method'] : false;
$results = false;
$spell_error = isset($_REQUEST['spell_error']) ? $_REQUEST['spell_error'] : false;

if ($query)
{
  $query2 = explode( ' ', $query );
  $correct = "";
  foreach ($query2 as &$value) {
    $correct .= SpellCorrector::correct($value);
    $correct .= " ";
  }
  $correct = substr($correct, 0, -1);

  // The Apache Solr Client library should be on the include path
  // which is usually most easily accomplished by placing in the
  // same directory as this script ( . or current directory is a default
  // php include path entry in the php.ini)
  require_once('Apache/Solr/Service.php');

  // create a new solr service instance - host, port, and webapp
  // path (all defaults in this example)
  if($method == "Lucene"){
    $additionalParameters = array(
      'fl' => array(
        'title',
        'og_url',
        'id',
        'description'
      )
    );
    $solr = new Apache_Solr_Service('localhost', 8983, '/solr/csci572_hw5_lucene');
  }
  if($method == "PageRank") {
    $additionalParameters = array(
      'sort' => 'pageRankFile desc',
      'fl' => array(
        'title',
        'og_url',
        'id',
        'description'
      )
    );
    $solr = new Apache_Solr_Service('localhost', 8983, '/solr/hw5');
  }

  if(! $solr -> ping()) {
    echo 'Solr service not responding.';
    exit;
  }

  // if magic quotes is enabled then stripslashes will be needed
  if (get_magic_quotes_gpc() == 1)
  {
    $correct = stripslashes($correct);
  }

  // in production code you'll always want to use a try /catch for any
  // possible exceptions emitted  by searching (i.e. connection
  // problems or a query parsing error)
  try
  {
    $results = $solr->search($correct, 0, $limit, $additionalParameters);
    $results_q = $solr->search($query, 0, $limit, $additionalParameters);
  }
  catch (Exception $e)
  {
    // in production you'd probably log or email this error to an admin
    // and then show a special message to the user but for this example
    // we're going to show the full exception
    die("<html><head><title>SEARCH EXCEPTION</title><body><pre>{$e->__toString()}</pre></body></html>");
  }
}

?>
<html>
  <head>
    <title>PHP Solr Client</title>
    <link rel="stylesheet" href="//apps.bdimg.com/libs/jqueryui/1.10.4/css/jquery-ui.min.css">
    <script src="//apps.bdimg.com/libs/jquery/1.10.2/jquery.min.js"></script>
    <script src="//apps.bdimg.com/libs/jqueryui/1.10.4/jquery-ui.min.js"></script>
    <link rel="stylesheet" href="jqueryui/style.css">
    <script>
       $(function() {
         var URL_PREFIX = "http://localhost:8983/solr/csci572/suggest?q=";
         var URL_SUFFIX = "&wt=json&indent=true";
         var count=0;
         var tags = [];
         $("#q").autocomplete({
           source : function(request, response) {
             var correct="",before="";
             var query = $("#q").val().toLowerCase();
             var character_count = query.length - (query.match(/ /g) || []).length;
             var space =  query.lastIndexOf(' ');
             if(query.length-1>space && space!=-1){
              correct=query.substr(space+1);
              before = query.substr(0,space);
            }
            else{
              correct=query.substr(0);
            }
            var URL = URL_PREFIX + correct+ URL_SUFFIX;
            $.ajax({
             url : URL,
             success : function(data) {
              var js =data.suggest.suggest;
              var docs = JSON.stringify(js);
              var jsonData = JSON.parse(docs);
              var result =jsonData[correct].suggestions;
              var j=0;
              var stem =[];
              for(var i=0;i<11 && j<result.length;i++,j++){
                if(result[j].term==correct)
                {
                  i--;
                  continue;
                }
                for(var k=0;k<i && i>0;k++){
                  if(tags[k].indexOf(result[j].term) >=0){
                    i--;
                    continue;
                  }
                }
                if(result[j].term.indexOf('.')>=0 || result[j].term.indexOf('_')>=0)
                {
                  i--;
                  continue;
                }
                var s =(result[j].term);
                if(stem.length == 8)
                  break;
                if(stem.indexOf(s) == -1)
                {
                  stem.push(s);
                  if(before==""){
                    tags[i]=s;
                  }
                  else
                  {
                    tags[i] = before+" ";
                    tags[i]+=s;
                  }
                }
              }
              console.log(tags);
              response(tags);
            },
            dataType : 'jsonp',
            jsonp : 'json.wrf'
          });
          },
          minLength : 1
        })
       });
     </script>
  </head>
  <body>
    <div class="ui-widget">
      <form  accept-charset="utf-8" method="get">
        <label for="q">Search:</label>
        <input id="q" name="q" type="text" value="<?php echo htmlspecialchars($query, ENT_QUOTES, 'utf-8'); ?>"/>
        <input type="submit"/>
        <br/>
        Ranking Method:
        <input type="radio" name="method"
        <?php if (isset($method) && $method=="Lucene") echo "checked";?>
        value="Lucene" CHECKED>Lucene(Default)
        <input type="radio" name="method"
        <?php if (isset($method) && $method=="PageRank") echo "checked";?>
        value="PageRank">PageRank
      </form>
    </div>

<?php
// display results
if($spell_error==true && $results_q) {
  // $total = (int) $results->response->numFound;
  $start = min(1, $retrieve_total);
  $end = min($limit, $retrieve_total);
  $mean = isset($_REQUEST['mean']) ? $_REQUEST['mean'] : false;
?>
  <form  accept-charset="utf-8" method="get">
    Did you mean:
    <a href="hello_snippets.php?q=<?php echo $mean ?>&method=<?php echo $method ?>">
      <strong><i><?php echo htmlspecialchars($mean, ENT_NOQUOTES, 'utf-8'); ?></i></strong>
    </a>
  </form>
<ol>
<?php
  // iterate result documents
  foreach ($results_q->response->docs as $doc)
  {
?>
      <li>

        <table style="border: 1px solid black; text-align: left">
<?php
    // iterate document fields / values
      $title = $doc -> title;
      $id = $doc -> id;
      $description = $doc -> description;

      $root_dir = "/Users/vickie/Documents/BigData/solr-7.1.0/BG/";
      $file_name = str_replace($root_dir, "", $id);
      $url = $url_array[$file_name];

      $snippet = getSnippet($id, $query);
?>
          <tr>
            <th>Title</th>
            <td>
              <a href="<?php echo $url; ?>">
                <?php echo htmlspecialchars($title, ENT_NOQUOTES, 'utf-8'); ?>
              </a>
            </td>
          </tr>
          <tr>
            <th>URL</th>
            <td>
              <a href="<?php echo $url; ?>">
                <?php echo htmlspecialchars($url, ENT_NOQUOTES, 'utf-8'); ?>
              </a>
            </td>
          </tr>
          <tr>
            <th>ID</th>
            <td><?php echo htmlspecialchars($id, ENT_NOQUOTES, 'utf-8'); ?></td>
          </tr>
          <tr>
            <th>Description</th>
            <td><?php echo htmlspecialchars($description, ENT_NOQUOTES, 'utf-8'); ?></td>
          </tr>
          <tr></tr>
          <!-- snippet -->
          <tr>
            <th>Snippet</th>
            <td><?php echo htmlspecialchars($snippet, ENT_NOQUOTES, 'utf-8'); ?></td>
          </tr>
        </table>
      </li>
  <?php
    }
  ?>
    </ol>
<?php
}
?>

<?php
if ($results && $spell_error==false)
{
  // $total = (int) $results->response->numFound;
  $start = min(1, $retrieve_total);
  $end = min($limit, $retrieve_total);
?>

<?php
  if($query<>$correct) {
?>
    <form  accept-charset="utf-8" method="get">
      Showing results for
        <a href="hello_snippets.php?q=<?php echo $correct ?>&method=<?php echo $method ?>">
          <strong><i><?php echo htmlspecialchars($correct, ENT_NOQUOTES, 'utf-8'); ?></i></strong>
        </a>
      <br/>
      Search instead for
        <a href="hello_snippets.php?spell_error=true&q=<?php echo $query ?>&method=<?php echo $method ?>&mean=<?php echo $correct?>">
          <?php echo htmlspecialchars($query, ENT_NOQUOTES, 'utf-8'); ?>
        </a>
    </form>

<?php
  }
?>
<ol>
<?php
  // iterate result documents
  foreach ($results->response->docs as $doc)
  {
?>
      <li>

        <table style="border: 1px solid black; text-align: left">
<?php
    // iterate document fields / values
      $title = $doc -> title;
      $id = $doc -> id;
      $description = $doc -> description;

      $root_dir = "/Users/vickie/Documents/BigData/solr-7.1.0/BG/";
      $file_name = str_replace($root_dir, "", $id);
      $url = $url_array[$file_name];

      $snippet = getSnippet($id, $correct);
?>

          <tr>
            <th>Title</th>
            <td>
              <a href="<?php echo $url; ?>">
                <?php echo htmlspecialchars($title, ENT_NOQUOTES, 'utf-8'); ?>
              </a>
            </td>
          </tr>
          <tr>
            <th>URL</th>
            <td>
              <a href="<?php echo $url; ?>">
                <?php echo htmlspecialchars($url, ENT_NOQUOTES, 'utf-8'); ?>
              </a>
            </td>
          </tr>
          <tr>
            <th>ID</th>
            <td><?php echo htmlspecialchars($id, ENT_NOQUOTES, 'utf-8'); ?></td>
          </tr>
          <tr>
            <th>Description</th>
            <td><?php echo htmlspecialchars($description, ENT_NOQUOTES, 'utf-8'); ?></td>
          </tr>
          <tr></tr>
          <!-- snippet -->
          <tr>
            <th>Snippet</th>
            <td><?php echo htmlspecialchars($snippet, ENT_NOQUOTES, 'utf-8'); ?></td>
          </tr>
        </table>
      </li>
  <?php
    }
  ?>
    </ol>
<?php
}
?>
  </body>
</html>

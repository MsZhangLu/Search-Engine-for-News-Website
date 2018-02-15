<?php

// make sure browsers see this page as utf-8 encoded HTML 编码
header('Content-Type: text/html; charset=utf-8');

$limit = 10;
$retrieve_total = 10;
$query = isset($_REQUEST['q']) ? $_REQUEST['q'] : false;
$method = isset($_REQUEST['method']) ? $_REQUEST['method'] : false;
$results = false;

if ($query)
{
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
    $solr = new Apache_Solr_Service('localhost', 8983, '/solr/csci572');
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
    $solr = new Apache_Solr_Service('localhost', 8983, '/solr/csci572_pageRank');
  }

  if(! $solr -> ping()) {
    echo 'Solr service not responding.';
    exit;
  }

  // if magic quotes is enabled then stripslashes will be needed
  if (get_magic_quotes_gpc() == 1)
  {
    $query = stripslashes($query);
  }

  // in production code you'll always want to use a try /catch for any
  // possible exceptions emitted  by searching (i.e. connection
  // problems or a query parsing error)
  try
  {
    $results = $solr->search($query, 0, $limit, $additionalParameters);
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
  </head>
  <body>
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
<?php

// display results
if ($results)
{
  // $total = (int) $results->response->numFound;
  $start = min(1, $retrieve_total);
  $end = min($limit, $retrieve_total);
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
    // foreach ($doc as $field => $value)
    // {
      $title = $doc -> title;
      $url = $doc -> og_url;
      $id = $doc -> id;
      $description = $doc -> description;
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

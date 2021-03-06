import edu.uci.ics.crawler4j.crawler.CrawlConfig;
import edu.uci.ics.crawler4j.crawler.CrawlController;
import edu.uci.ics.crawler4j.fetcher.PageFetcher;
import edu.uci.ics.crawler4j.robotstxt.RobotstxtConfig;
import edu.uci.ics.crawler4j.robotstxt.RobotstxtServer;

public class Controller {

    public static void main(String[] args) throws Exception {
        // TODO Auto-generated method stub
        String crawlStorageFolder = "/Users/vickie/data/crawl";
        int numberOfCrawlers = 7;
        int maxPagesToFetch = 20000;
        int maxDepthOfCrawling = 16;
//        int politenessDelay = 1500;

        CrawlConfig config = new CrawlConfig();
        config.setCrawlStorageFolder(crawlStorageFolder);
        config.setMaxPagesToFetch(maxPagesToFetch);
        config.setMaxDepthOfCrawling(maxDepthOfCrawling);
        config.setMaxDownloadSize(1073661);
        config.setIncludeBinaryContentInCrawling(true);
//        config.setPolitenessDelay(politenessDelay);

		/*
		 * Instantiate the controller for this crawl.
		 */
        PageFetcher pageFetcher = new PageFetcher(config);
        RobotstxtConfig robotstxtConfig = new RobotstxtConfig();
        RobotstxtServer robotstxtServer = new RobotstxtServer(robotstxtConfig, pageFetcher);
        CrawlController controller = new CrawlController(config, pageFetcher, robotstxtServer);
		/*
		 * For each crawl, you need to add some seed urls. These are the first URLs that
		 * are fetched and then the crawler starts following links which are found in
		 * these pages
		 */
        controller.addSeed("https://www.bostonglobe.com/");

		/*
		 * Start the crawl. This is a blocking operation, meaning that your code will
		 * reach the line after this only when crawling is finished.
		 */
        controller.start(MyCrawler.class, numberOfCrawlers);

    }

}

import java.io.BufferedWriter;
import java.io.File;
import java.io.FileWriter;
import java.io.IOException;
import java.util.Set;
import java.util.regex.Pattern;

import edu.uci.ics.crawler4j.crawler.Page;
import edu.uci.ics.crawler4j.crawler.WebCrawler;
import edu.uci.ics.crawler4j.parser.BinaryParseData;
import edu.uci.ics.crawler4j.parser.HtmlParseData;
import edu.uci.ics.crawler4j.url.WebURL;

public class MyCrawler extends WebCrawler {
    private final static Pattern EXTENSIONS = Pattern.compile(".*(\\.(html?|doc|pdf|bmp|gif|jpe?g|png|tiff?))$");


    @Override
    public boolean shouldVisit(Page referringPage, WebURL url) {

        Boolean flag = false;

        String url_s = url.getURL();
        if(url_s.contains(",")) {
            url_s = url_s.replaceAll(",", "_");
        }

        String href = url_s.toLowerCase();
        if(href.startsWith("https://www.bostonglobe.com/") || href.startsWith("http://www.bostonglobe.com/") ) {
            flag = true;
        }

        File urls_csv = new File("./urls_Boston_Globe.csv");
        try {
            BufferedWriter urls_bw = new BufferedWriter(new FileWriter(urls_csv, true));
            if(flag == true) {
                urls_bw.write(url_s + ", OK");
            } else {
                urls_bw.write(url_s + ", N_OK");
            }
            urls_bw.newLine();
            urls_bw.close();
        } catch (IOException e) {
            // TODO Auto-generated catch block
            e.printStackTrace();
        }

        if(flag == true && (EXTENSIONS.matcher(href).matches() || !href.substring(href.length()-5, href.length()).contains("."))) {
            return true;
        }

        return false;
    }

    @Override
    protected void handlePageStatusCode(WebURL webUrl, int statusCode, String statusDescription) {
        String url_s = webUrl.getURL();
        if(url_s.contains(",")) {
            url_s = url_s.replaceAll(",", "_");
        }
        File fuck_csv = new File("./fetch_Boston_Globe.csv");

        try {
            BufferedWriter fetch_bw = new BufferedWriter(new FileWriter(fuck_csv, true));
            fetch_bw.write(url_s  + ", " + statusCode);
            fetch_bw.newLine();
            fetch_bw.close();

        } catch (IOException e1) {
            // TODO Auto-generated catch block
            e1.printStackTrace();
        }
    }

    @Override
    public void visit(Page page) {


        String url = page.getWebURL().getURL();
        System.out.println(url);

        int statusCode = page.getStatusCode();

        if(url.contains(",")) {
            url = url.replaceAll(",", "_");
        }


        if(statusCode >= 200 || statusCode < 300) {

            File visit_csv = new File("./visit_Boston_Globe.csv");
            if(page.getParseData() instanceof BinaryParseData) {
                BinaryParseData binaryParseData = (BinaryParseData) page.getParseData();
                String html = binaryParseData.getHtml();
                int fileSize = html.length();
                Set<WebURL> links = binaryParseData.getOutgoingUrls();
                String contentType = page.getContentType();

                try {
                    BufferedWriter visit_bw = new BufferedWriter(new FileWriter(visit_csv, true));
                    visit_bw.write(url + ", " + fileSize + ", "+ links.size() + ", " + contentType);
                    visit_bw.newLine();
                    visit_bw.close();
                } catch (IOException e) {
                    // TODO Auto-generated catch block
                    e.printStackTrace();
                }
            } else if (page.getParseData() instanceof HtmlParseData){
                HtmlParseData htmlParseData = (HtmlParseData) page.getParseData();
                String html = htmlParseData.getHtml();
                int fileSize = html.length();
                Set<WebURL> links = htmlParseData.getOutgoingUrls();
                String contentType = page.getContentType();

                if(contentType.toLowerCase().contains("text/html")) {
                    contentType = "text/html";
                }

                try {
                    BufferedWriter visit_bw = new BufferedWriter(new FileWriter(visit_csv, true));
                    visit_bw.write(url + ", " + fileSize + ", "+ links.size() + ", " + contentType);
                    visit_bw.newLine();
                    visit_bw.close();
                } catch (IOException e) {
                    // TODO Auto-generated catch block
                    e.printStackTrace();
                }
            }

        }
    }



}
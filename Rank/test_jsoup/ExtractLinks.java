package test_jsoup;

import java.io.BufferedReader;
import java.io.BufferedWriter;
import java.io.File;
import java.io.FileOutputStream;
import java.io.FileReader;
import java.io.FileWriter;
import java.io.IOException;
import java.io.OutputStreamWriter;
import java.io.PrintWriter;
import java.util.HashMap;
import java.util.HashSet;
import java.util.Iterator;
import java.util.Map;
import java.util.Set;

import org.jsoup.Jsoup;
import org.jsoup.nodes.Document;
import org.jsoup.nodes.Element;
import org.jsoup.select.Elements;

public class ExtractLinks {

	public static void main(String[] args) throws IOException {
		// TODO Auto-generated method stub
//		File file = new File("./BG/BG/015059b5dbb3abece09f607015b8e5f39524df20f6bfc72d0f6d89e55f3e7600.html");
//		Document doc = Jsoup.parse(file, "UTF-8", "https://www.bostonglobe.com/");
//		
//		Elements links = doc.select("a[href]");
//		Elements media = doc.select("[src]");
//		Elements imports = doc.select("link[href]");
		
//		print("\nMedia: (%d)", media.size());
//		for (Element src : media) {
//			if(src.tagName().equals("img"))
//				print(" * %s: <%s> %s%s (%s)",
//						src.tagName(), src.attr("abs:src"), src.attr("width"), 
//						src.attr("height"), trim(src.attr("alt"), 20));
//			else
//				print(" * %s: <%s>", src.tagName(), src.attr("abs:src"));
//		}
//		
//		print("\nImports: (%d)", imports.size());
//		for (Element link : imports) {
//			print(" * %s <%s> (%s)", link.tagName(), link.attr("abs:href"), link.attr("rel"));
//		}
		
//		print("\nLinks: (%d)", links.size());
//		for (Element link : links) {
//			print(" * a: <%s> (%s)", link.attr("abs:href"), trim(link.text(), 35));
//		}
//		
		
		String csvFile = "./BG/Boston Global Map.csv";
		BufferedReader br = null;
		final String DELIMITER = ",";
		String line = "";
		Map<String, String> urlFileMap = new HashMap<String, String>();
		Map<String, String> fileUrlMap = new HashMap<String, String>();
		try {
			br = new BufferedReader(new FileReader(csvFile));
			while((line = br.readLine()) != null) {
				String[] tokens = line.split(DELIMITER);
				urlFileMap.put(tokens[1], tokens[0]);
				fileUrlMap.put(tokens[0], tokens[1]);
			}
		} catch (Exception e) {
			e.printStackTrace();
		} finally {
            try {
                br.close();
            } catch (IOException e) {
                e.printStackTrace();
            }
        }
		
		File dir = new File("./BG/BG");
		Set<String> edges = new HashSet<String>();
		
		for (File file : dir.listFiles()) {
			if(file.getName().equals(".DS_Store"))
				continue;
			// parse(String html, String baseUri): 
			//   html - HTML to parse; 
			//   baseUri - the url where the html was retrived from. resolve relative urls to absolute, 就是该地址是从哪个网页爬取的
			Document doc = Jsoup.parse(file, "UTF-8", fileUrlMap.get(file.getName()));
			Elements links = doc.select("a[href]");

			for(Element link: links) { 
				String url = link.attr("abs:href");
				if(urlFileMap.containsKey(url)) {
					edges.add(file.getName() + " " + urlFileMap.get(url));
				}
			}
		}

		try {
			save(edges, "./BG/edgeList.txt");
		} catch (Exception e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
	}
	
	public static void save(Set<String> obj, String path) throws Exception {
	    PrintWriter pw = null;
	    try {
	        pw = new PrintWriter(
	            new OutputStreamWriter(new FileOutputStream(path), "UTF-8"));
	        for (String s : obj) {
	            pw.println(s);
	        }
	        pw.flush();
	    } finally {
	        pw.close();
	    }
	}
	
	

//	private static void print(String meg, Object...args) {
//		// TODO Auto-generated method stub
//		System.out.println(String.format(meg, args));
//	}
//
//	private static String trim(String s, int width) {
//		// TODO Auto-generated method stub
//		if(s.length() > width)
//			return s.substring(0, width - 1) + ".";
//		else
//			return s;
//	}
	
	
}

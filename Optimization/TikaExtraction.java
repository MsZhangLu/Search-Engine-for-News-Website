package test_tika;

import java.io.File;
import java.io.FileInputStream;
import java.io.FileOutputStream;
import java.io.IOException;
import java.io.OutputStreamWriter;
import java.io.PrintWriter;
import java.util.HashSet;
import java.util.Set;

import org.apache.tika.exception.TikaException;
import org.apache.tika.metadata.Metadata;
import org.apache.tika.parser.ParseContext;
import org.apache.tika.parser.html.HtmlParser;
import org.apache.tika.sax.BodyContentHandler;
import org.xml.sax.SAXException;


public class TikaExtraction {

	public String parseExample(String path) throws IOException, SAXException, TikaException {
		// detecting the file type
		BodyContentHandler handler = new BodyContentHandler(-1);
		Metadata metadata = new Metadata();
		FileInputStream inputstream = new FileInputStream(new File(path));
		ParseContext pcontext = new ParseContext();

		// Html parser
		HtmlParser htmlparser = new HtmlParser();
		htmlparser.parse(inputstream, handler, metadata, pcontext);

		String content = handler.toString();
		content = content.replaceAll("[ \\t\\n\\x0B\\f\\r]", " ");
		String words[] = content.split(" ");
		String content_filterd = "";
		for(String word : words) {
			if(word.matches("[\\(]?+[a-zA-Z]+[\\.\\:\\,\\)\\!\\?]?")) {
				content_filterd.replaceAll("[\\(\\)\\.\\:\\,\\!\\?]", "");
				content_filterd += word + " ";
			} 
		}
		return content_filterd + "\n\n";
	}

	public static void main(final String[] args) throws IOException, TikaException, SAXException {

		File folder = new File("./BG/BG");
		File[] listOfFiles = folder.listFiles();

		TikaExtraction tikaExtraction = new TikaExtraction();

		Set<String> content = new HashSet<String>();

		for (int i = 0; i < listOfFiles.length; i++) {
			System.out.println(i);
			String path = "./BG/BG/" + listOfFiles[i].getName();
			String filecontent = tikaExtraction.parseExample(path);
			content.add(filecontent);
		}

		try {
			save(content, "./BG/output_10.txt");
		} catch (Exception e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}

	}

	public static void save(Set<String> obj, String path) throws Exception {
		PrintWriter pw = null;
		try {
			pw = new PrintWriter(new OutputStreamWriter(new FileOutputStream(path), "UTF-8"));
			for (String s : obj) {
				pw.println(s);
			}
			pw.flush();
		} finally {
			pw.close();
		}
	}
}

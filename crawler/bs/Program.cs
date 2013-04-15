using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Net;
using System.Text.RegularExpressions;
using System.IO;

namespace fs_crawler
{
    class Program
    {
        public static WebClient WEB;
        public static TextWriter[] FILE = new TextWriter[3];

        public static string serie_names;
        public static int serie_seasons = 1;
        public static string[] hosters = new string[12];
        public static string[] hoster_list = new string[12] {"PutLocker","Sockshare","Streamcloud","BitShare","Ecostream","FileNuke","Primeshare","YouTube","VideoWeed","UploadC","RapidVideo","DivxStage"};
        public static string[] hoster_regex = new string[12]
            {
                "http://www.putlocker.com/embed/([0-9a-zA-Z]{16})",
                "http://www.sockshare.com/embed/([0-9a-zA-Z]{16})",
                "http://streamcloud.eu/([0-9a-zA-Z]{12})",
                "http://bitshare.com/files/([0-9a-zA-Z]{8})",
                "http://www.ecostream.tv/stream/([0-9a-zA-Z]{32})",
                "http://filenuke.com/([0-9a-zA-Z]{12})",
                "http://primeshare.tv/download/([0-9a-zA-Z]{10})",
                "https://www.youtube-nocookie.com/embed/([0-9a-zA-Z]{11})",
                "http://videoweed.es/file/([0-9a-zA-Z]{13})",
                "http://www.uploadc.com/([0-9a-zA-Z]{12})",
                "http://www.rapidvideo.com/embed/([0-9a-zA-Z]{8})",
                "http://embed.divxstage.eu/embed.php?v=([0-9a-zA-Z]{13})"
            };

        static void Main(string[] args)
        {
            FILE[0] = new StreamWriter("execution.log");
            WEB = new WebClient();
            WEB.Encoding = System.Text.Encoding.UTF8;
            WEB.Headers.Add("user-agent", "Mozilla/5.0 (Windows; Windows NT 5.1; rv:1.9.2.4) Gecko/20100611 Firefox/3.6.4");

            PROGRAM_START:
            WriteLog("...starte fs_crawler v1.0\nGib den Namen der Serie ein:");
            serie_names = Console.ReadLine();
            if (serie_names.Length == 0)
            {
                serie_names = "";
                foreach (Match match in Regex.Matches(WEB.DownloadString("https://www.burning-seri.es/serie-alphabet"), "(?is)<a href=\"serie/(.*?)\">.*?</a>"))
                {
                    if (match.Success)
                    {
                        bool odd = true;
                        foreach (Group group in match.Groups)
                        {
                            odd = !odd;
                            if (odd) serie_names = serie_names + "," + group.Value;
                        }
                    }
                }
                WriteLog("Es werden alle Serien überprüft: " + serie_names.Split(',').Length + " Stück");
            }
            else WriteLog("Folgende Serien werden überprüft: " + serie_names + " (" + serie_names.Split(',').Length + ")");

            WriteLog("Gib die zu prüfenden Hoster mit einem Komma getrennt ein.\nVerfügbare Hoster:");
            for (int i = 0; i < hoster_list.Length; i++) WriteLog(i + " - " + hoster_list[i]);
            string tmp_hosters = Console.ReadLine();

            if (tmp_hosters.Length == 0) tmp_hosters = "0,1,2,3,4,5,6,7,8,9,10,11";
            hosters = tmp_hosters.Split(',');
            string[] serie_names_array = serie_names.Split(',');
            serie_names_array = serie_names_array.Where(val => val.Length >= 1).ToArray();

            int serie_loop = 0;
            foreach (string serie_name in serie_names_array)
            {
                serie_loop++;

                if (serie_name.Length == 0) continue;
                WriteLog("...prüfe Serie " + serie_name + " - " + serie_loop + "/" + serie_names_array.Length);

                FILE[1] = new StreamWriter("data/" + serie_name.Replace("-", "_").ToLower() + "_episodes.csv");
                FILE[2] = new StreamWriter("data/" + serie_name.Replace("-", "_").ToLower() + "_links.sql");
                WEB = new WebClient();
                WEB.Encoding = System.Text.Encoding.UTF8;
                WEB.Headers.Add("user-agent", "Mozilla/5.0 (Windows; Windows NT 5.1; rv:1.9.2.4) Gecko/20100611 Firefox/3.6.4");
                string html = WEB.DownloadString("https://www.burning-seri.es/serie/" + serie_name + "/1");

                WriteLog("...rufe Serieninformationan ab");
                serie_seasons = 1;
                while (true)
                {
                    if (html.IndexOf("<a href=\"serie/" + serie_name + "/" + (serie_seasons + 1) + "\">" + (serie_seasons + 1) + "</a>") >= 1) serie_seasons++;
                    else break;
                }
                WriteLog("\t..." + serie_seasons + " Staffeln erkannt");
                string pattern = "";

                for (int season = 1; season <= serie_seasons; season++)
                {
                    WriteLog("\t...prüfe Staffel " + season + "/" + serie_seasons);
                    if (season != 1) html = WEB.DownloadString("https://www.burning-seri.es/serie/" + serie_name + "/" + season);

                    WriteLog("\t\t...rufe Episodeninformationen ab");

                    if (html.IndexOf("<span lang=\"en\">") >= 1)
                    {
                        pattern = "(?is)<a href=\"serie/" + serie_name + "/" + season + "/(.*?)-.*?\">.*?<strong>(.*?)</strong>.*?<span lang=\"en\">(.*?)</span>.*?</a>";
                        foreach (Match match in Regex.Matches(html, pattern))
                        {
                            if (match.Success)
                            {
                                byte loop = 0;
                                byte episode = 0;
                                string[] episode_names = new string[2] { "Unbekannt", "Unknown" };
                                foreach (Group group in match.Groups)
                                {
                                    if (loop == 4) break;
                                    switch (loop++)
                                    {
                                        case 1:
                                        {
                                            episode = Convert.ToByte(group.Value);
                                            break;
                                        }
                                        case 2:
                                        {
                                            episode_names[0] = group.Value;
                                            episode_names[0] = episode_names[0].Replace("\"", "\"\"");
                                            break;
                                        }
                                        case 3:
                                        {
                                            episode_names[1] = group.Value;
                                            episode_names[1] = episode_names[1].Replace("\"", "\"\"");
                                            FILE[1].WriteLine(season + ";" + episode + ";" + "\"" + episode_names[0] + "\"" + ";" + "\"" + episode_names[1] + "\"");
                                            break;
                                        }
                                    }
                                }
                            }
                        }
                    }
                    WriteLog("\t\t...rufe Linkidentifikationen ab");

                    foreach (string hoster in hosters)
                    {
                        byte hoster_id = Byte.Parse(hoster);
                        WriteLog("\t\t\t...prüfe Hoster " + hoster_list[hoster_id]);
                        pattern = "serie/.*?/" + season + "/([0-9]+)-.+/" + hoster_list[hoster_id] + "-1";
                        foreach (Match match in Regex.Matches(html, pattern))
                        {
                            bool is_odd = true;
                            string tmp_path = "";
                            foreach (Group group in match.Groups)
                            {
                                is_odd = !is_odd;
                                if (is_odd)
                                {
                                    string catch_link_page = WEB.DownloadString("http://www.burning-seri.es/" + tmp_path);
                                    Match match2 = Regex.Match(catch_link_page, hoster_regex[hoster_id]);
                                    FILE[2].WriteLine(season + ";" + group.Value + ";" + (hoster_id + 1) + ";\"" + match2.Groups[1].Value + "\"");
                                }
                                else tmp_path = group.Value;
                            }
                        }
                    }
                }
            }
            WriteLog("Serieninformationen erfolgreich geladen");
            FILE[1].Close();
            FILE[2].Close();
            goto PROGRAM_START;
        }

        public static void WriteLog(string logdata)
        {
            FILE[0].WriteLine(logdata);
            Console.WriteLine(logdata);
        }
    }
}
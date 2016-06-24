
import urllib
from django.utils import simplejson as json
from google.appengine.ext import db
from google.appengine.ext import webapp
from google.appengine.ext.webapp.util import run_wsgi_app
import logging


def send_message(message):
    # grp_api_uri = 'http://api.txtweb.com/groups?action=group_push&txtweb-group-id=twgroup-54911cc5e4b07d3288a8f9fd&txtweb-message=<html><head><meta%20name=txtweb-appkey%20content="d1ce380e-f94d-4288-9d73-3efef6d58df7"%20></head><body>' + urllib.quote_plus(message) + "</body></html>"
    # push_api_uri = "http://scripush.appspot.com/servletex?message=" + urllib.quote_plus(message)
    # logging.info(urllib.urlopen(grp_api_uri).read())
    # logging.info(urllib.urlopen(push_api_uri).read())
    print "TO be implemented with twilio"
    
class LastUpdated(db.Model):
    """Sub model for representing an author."""
    timestamp = db.StringProperty(indexed=False)
    news_timestamp = db.StringProperty(indexed=False)

class NewsPage(webapp.RequestHandler):
    def get(self):
        self.response.headers['Content-Type'] = 'text/html'
        data = json.loads(urllib.urlopen("http://mapps.cricbuzz.com/cricbuzz-android/news/index").read())
        
        if self.has_updated(data.get('stories')[0].get('header').get('date')):
            message = data.get('stories')[0].get('hline').upper() + "<br><br>" + data.get('stories')[0].get('intro')
            logging.info(message)
            send_message(message)
        else:
            logging.info("News not updated")
        self.response.out.write(data)
        self.response.set_status(200)
        
    def has_updated(self, time_stamp):
        key = db.Key.from_path('LastUpdated', 5629499534213120)
        LastUpdated_query = LastUpdated.get(key)
         
 
        if LastUpdated_query.news_timestamp == time_stamp:
            return False
        else:
            LastUpdated_query.news_timestamp = time_stamp
            LastUpdated_query.put()
            return True
        
    



class MainPage(webapp.RequestHandler):

    def get_add_status(self,match_url):
        url = "http://mapps.cricbuzz.com/cbzandroid/3.0/match/%scommentary.json" % match_url
        data = json.loads(urllib.urlopen(url).read())
        return data.get('header').get('status')
      
    def get_scores(self, match_id):
        data = json.loads(urllib.urlopen("http://mapps.cricbuzz.com/cbzandroid/2.0/livematches.json").read())
        
        scores, header = '', ''
        for ele in data:
            logging.info(ele.get("matchId") + " " + match_id)
            if str(ele.get("matchId")) == str(match_id):
                if ele.get('header').get('type') == "TEST":
                    status = self.get_add_status(ele.get('datapath'))
                else:               
                    status = ele.get('header').get('status')
                header = " (" + ele.get('header').get("mchDesc") + ", " + ele.get('header').get("mnum") + ")"
                if ele.get('miniscore', None) is not None:
                    batteamscore = ele.get('miniscore').get('batteamscore') + " (" + ele.get('miniscore').get('overs') + ")"
                    bowlteamscore = ele.get('miniscore').get('bowlteamscore') + " (" + ele.get('miniscore').get('bowlteamovers') + ")"
                    
                    if "(0)" in bowlteamscore:
                        bowlteamscore = "Yet to bat"
                        
                    if ele.get('team1').get('id') == ele.get('miniscore').get('batteamid'):
                        batteamname = ele.get('team1').get('sName')
                        bowlteamname = ele.get('team2').get('sName')
                    else:
                        batteamname = ele.get('team2').get('sName')
                        bowlteamname = ele.get('team1').get('sName')
                    batobj = ele.get('miniscore').get('striker')
                    striker = batobj.get('fullName') + "*: " + batobj.get('runs') + "(" + batobj.get('balls') + ")"
                    batobj = ele.get('miniscore').get('nonStriker')
                    non_striker = batobj.get('fullName') + ": " + batobj.get('runs') + "(" + batobj.get('balls') + ")"
                    
                    bowlobj = ele.get('miniscore').get('bowler')                   
                    bowler = bowlobj.get('fullName') + "*: " + bowlobj.get("overs") + "-" + bowlobj.get("maidens") + "-" + bowlobj.get("runs") + "-" + bowlobj.get("wicket")                        
                    bowlobj = ele.get('miniscore').get('nsbowler')                   
                    nsbowler = bowlobj.get('fullName') + ": " + bowlobj.get("overs") + "-" + bowlobj.get("maidens") + "-" + bowlobj.get("runs") + "-" + bowlobj.get("wicket")                      
                    
                    scores += str(bowlteamname) + " - " + str(bowlteamscore) + "<br>" + str(batteamname) + " - " + str(batteamscore) 
                    scores += "<br>" + striker + "<br>" + non_striker + "<br>" + bowler + "<br>" + nsbowler + "<br>" + str(status)
                    break
        logging.info(header + " " + scores)            
        return header, scores
    
    def get(self):
        self.response.headers['Content-Type'] = 'text/html'
        
        data = json.loads(urllib.urlopen("http://sms.cricbuzz.com/chrome/alert.json").read())
        logging.info(data)
#         scores= get_scores(data.get('matchId'))
        sub = data.get('sub')
        
        if self.has_updated(data.get('time')):          

            msg = data.get('msg')
            message=msg           
#             logging.info(sub)
            if sub in ['Score']:
                for el in ['Match','Summary','won',"Stumps","Lunch","Tea"]:
                    if el in msg:
                        message=msg
                        break
                else:
                    message=None
            if message: 
                header, scores = self.get_scores(data.get('matchId'))           
                if sub in ['Toss', 'News']:
#                     logging.info(sub)
                    scores = ''
                
                header = data.get("sub").upper() + header
                message = header + "<br>" + msg + "" + scores
                
                send_message(message)
            else:
                logging.info("Ignoring irrelevant updates")
        else:
            logging.info("Scores not updated")

    def has_updated(self, time_stamp):
        key = db.Key.from_path('LastUpdated', 5629499534213120)
        LastUpdated_query = LastUpdated.get(key)
         
 
        if LastUpdated_query.timestamp == time_stamp:
            return False
        else:
            LastUpdated_query.timestamp = time_stamp
            LastUpdated_query.put()
            return True

        
application = webapp.WSGIApplication([
    ('/', MainPage)
], debug=True)

news_application = webapp.WSGIApplication([
    ('/news', NewsPage)
], debug=True)

def main():
    if application:
        run_wsgi_app(application)
        
    if news_application:
        run_wsgi_app(news_application)
 
if __name__ == "__main__":
    main()

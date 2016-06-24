
from twilio.rest.client import TwilioRestClient
 
 
#----------------------------------------------------------------------
def send_sms(msg, to):
    sid = "AC4ec60036a7dce19b008bb82e5c2925f3"
    auth_token = "64a8aea84bb7c91da584d365960a45dd"
    twilio_number = "+1 256-344-8754"
 
    client = TwilioRestClient(sid, auth_token)
 
    message = client.messages.create(body=msg,
                                     from_=twilio_number,
                                     to=to,
                                     )
    return message 

# send_sms('Test', '+919620950489')

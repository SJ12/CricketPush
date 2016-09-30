
from twilio.rest.client import TwilioRestClient
 
 
#----------------------------------------------------------------------
def send_sms(msg, to):
    sid = "ACa88c986da84d12902419aeef203a8bac"
    auth_token = "d444c203619e8c114b29f9e32f3d1cef"
    twilio_number = "+14806464325"
 
    client = TwilioRestClient(sid, auth_token)
 
    message = client.messages.create(body=msg,
                                     from_=twilio_number,
                                     to=to,
                                     )
    return message 

# send_sms('Test', '+919620950489')

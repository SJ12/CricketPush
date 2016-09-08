
from twilio.rest.client import TwilioRestClient
 
 
#----------------------------------------------------------------------
def send_sms(msg, to):
    sid = "AC97b5731ee0f9dea61ec0abccb3b6fea6"
    auth_token = "2d58decb8e9a842132192cc59edd8b3d"
    twilio_number = "+1 201-244-4640"
 
    client = TwilioRestClient(sid, auth_token)
 
    message = client.messages.create(body=msg,
                                     from_=twilio_number,
                                     to=to,
                                     )
    return message 

# send_sms('Test', '+919620950489')

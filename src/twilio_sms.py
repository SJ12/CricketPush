
from twilio.rest.client import TwilioRestClient
 
 
#----------------------------------------------------------------------
def send_sms(msg, to):
    sid = "ACd3b796c78c0e4a3b28d6ba76c96ed25a"
    auth_token = "062fc5e05dee0b0fe06bcb56622274a6"
    twilio_number = "+1 267-873-4841"
 
    client = TwilioRestClient(sid, auth_token)
 
    message = client.messages.create(body=msg,
                                     from_=twilio_number,
                                     to=to,
                                     )
    return message 

# send_sms('Test', '+919620950489')

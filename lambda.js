'use strict';

const Alexa = require('alexa-sdk');

const AWS = require('aws-sdk');
const db = new AWS.DynamoDB.DocumentClient();

const http = require('https');
const amazonProfileURL = 'https://api.amazon.com/user/profile?access_token=';

var user_name = "";
var user_email = "";

const APP_ID = 'amzn1.ask.skill.9f039d36-3ef9-491d-989c-1c2736c097ea';

const common_phrases = {
        SKILL_NAME: 'Rememberall',
        REMIND_MESSAGE: 'Okay, I\'ll remind you to ',
        RECALL_MESSAGE: 'You wanted me to remind you to ',
        HELP_MESSAGE: 'Let me know whenever you want to be reminded of something.',
        HELP_REPROMPT: 'Is there something you would like me to remember right now?',
        STOP_MESSAGE: 'Goodbye!'
};

const handlers = {
    'LaunchRequest': function () {
        this.emit('AMAZON.HelpIntent');
    },
    'SetImmediateReminderIntent': function() {
        var d = new Date();
        var datestring = d.getFullYear() + "-" + ("0"+(d.getMonth()+1)).slice(-2) + "-" + ("0" + d.getDate()).slice(-2) + " " + ("0" + d.getHours()).slice(-2) + ":" + ("0" + d.getMinutes()).slice(-2) + ":" + ("0" + d.getSeconds()).slice(-2);
        var item = {
            TableName:'reminders',
            Item: {
                acct_email: user_email,
                timestamp: datestring,
                complete: false,
                self_flag: true,
                text: this.event.request.intent.slots.action.value,
                // assigned_caregiver: ""
                due: datestring
            }
        };
        console.log("Putting the following:")
        console.log(item);
        var response = db.put(item, (err, data) => {
            if (err) {
                console.log("PUT_ERROR: " + err);
            } else {
                console.log("PUT_SUCCESS: " + JSON.stringify(data));
            }
            this.emit('SetImmediateReminderSpeech');
        });
    },
    'SetImmediateReminderSpeech': function() {
        var stuff = this.event.request.intent.slots.action.value;
        this.emit(':tell', common_phrases.REMIND_MESSAGE + stuff);
        // this.context.done();
    },
    'RecallImmediateReminderIntent': function() {
        var params = {
            TableName: 'reminders',
            ProjectionExpression: "#acct_email, #timestamp, #self_flag, #text",
            KeyConditionExpression: "#acct_email = :email",
            ExpressionAttributeNames: {
                "#acct_email": "acct_email",
                "#timestamp": "timestamp",
                "#self_flag": "self_flag",
                "#text": "text"
            },
            ExpressionAttributeValues: {
                ":email": user_email,
            }
        };
        db.query(params, (err, data) => {
            if (err) {
                console.log("QUERY_ERROR: " + err);
                return;
            } else {
                console.log("QUERY_SUCCESS: " + data);
            }
            data.Items.sort((a, b) => {
                var keyA = new Date(a.timestamp);
                var keyB = new Date(b.timestamp);
                return keyB - keyA;
            })
            // var ret = JSON.parse(data.Items);
            var idx = 0;
            while (!data.Items[idx].self_flag) {
                idx++; // iterate through until we find the first self-reminder
            }

            if (idx >= data.Items.length) { // if there are no instant reminders
                this.emit('RecallReminderSpeech', "");
            } else { // speak the most recent one
                console.log("Got item: " + data.Items[idx].text);
                this.emit('RecallImmediateReminderSpeech', data.Items[idx].text);
            }
        });
    },
    'RecallImmediateReminderSpeech': function(reminder) {
        if (0 === reminder.length) {
            this.emit(':tell', 'You haven\'t set any reminders yet.');
        } else {
            this.emit(':tell', common_phrases.RECALL_MESSAGE + reminder);
        }
    },
    'GetStatusIntent': function() {
        // get email of requested person
        var requestedName = this.event.request.intent.slots.person.value.toLowerCase();
        console.log("Requested Name: " + requestedName);
        var params = {
            TableName: 'care_givers',
            ProjectionExpression: "care_giver_email",
            FilterExpression: "acct_email = :email AND begins_with(#name, :person)",
            // KeyConditionExpression: "acct_email = :email",
            ExpressionAttributeNames: {
                "#name": "lookup_name"
            },
            ExpressionAttributeValues: {
                ":person": requestedName,
                ":email": user_email
            }
        };
        db.scan(params, (err, data) => {
            if (err) {
                console.log("SCAN_ERROR: " + err);
                return;
            } else {
                console.log("SCAN_SUCCESS: " + data);
            }
            if (0 === data.Items.length) {
                this.emit('GetStatusSpeech', "", requestedName, "");
                return;
            }
            var email_assigned_to = data.Items[0].care_giver_email;
            console.log("Assignee email: " + email_assigned_to);

            var getStatus_params = {
                TableName: 'statuses',
                ProjectionExpression: "#text, #timestamp",
                FilterExpression: "acct_email = :email AND care_giver_email = :assigned_to",
                ExpressionAttributeValues: {
                    ":email": user_email,
                    ":assigned_to": email_assigned_to
                },
                ExpressionAttributeNames: {
                    "#text": "text",
                    "#timestamp": "timestamp"
                }
            }

            db.scan(getStatus_params, (err, data) => {
                if (err) {
                    console.log("GET_ERROR: " + err);
                    return;
                } else {
                    console.log("GET_SUCCESS: " + data);
                }

                if (0 === data.Items.length) {
                    this.emit('GetStatusSpeech', requestedName, "", "");
                    return;
                }
                data.Items.sort((a, b) => {
                    var keyA = new Date(a.timestamp);
                    var keyB = new Date(b.timestamp);
                    return keyB - keyA;
                })
                this.emit('GetStatusSpeech', requestedName, data.Items[0].text, "");
            })

        });
        // look up the requested person in the list
        // look up what they are doing currently
        // emit the response
    },
    // if name is empty, status is the name that could not be found
    'GetStatusSpeech': function(name, status, when) {
        console.log("Name: " + name);
        console.log("Status: " + status);
        console.log("When: " + when);
        if (0 === name.length) {
            this.emit(':tell', "Sorry, I'm not sure who " + status + " is");
        } else if (0 === status.length) {
            this.emit(':tell', "I couldn't find a status for " + name);
        } else {
            this.emit(':tell', name + " is " + status + " " + when);
        }
    },
    'SetCaregiverReminderIntent': function() {
        // if the reminder does not contain a name
            // put the reminder in the database directly
        // if it does contain a reminder
            // get a list of caregivers associated with the account
            // look up the person in the list
            // add their email under task assignment
            // put reminder in database
    },
    'AMAZON.HelpIntent': function () {
        const speechOutput = common_phrases.HELP_MESSAGE;
        const reprompt = common_phrases.HELP_MESSAGE;
        this.emit(':ask', speechOutput, reprompt);
    }
};


exports.handler = (event, context) => {
    const alexa = Alexa.handler(event, context);
    alexa.appId = APP_ID;
    // console.log(event);
    if (event.session.user.accessToken === undefined) {
        alexa.emit(':tellWithLinkAccountCard',
                   'To start using Rememberall, please use the Alexa companion app to authenticate on Amazon');
    } else {
        var body = '';
        var jsonObj = JSON.stringify(event);
        var queryURL = amazonProfileURL + event.session.user.accessToken;
        console.log("Querying: " + queryURL);
        http.get(queryURL, (res) => {
          const statusCode = res.statusCode;
          const contentType = res.headers['content-type'];

          let error;
          if (statusCode !== 200) {
            error = new Error(`Request Failed.\n` +
                              `Status Code: ${statusCode}`);
          } else if (!/^application\/json/.test(contentType)) {
            error = new Error(`Invalid content-type.\n` +
                              `Expected application/json but received ${contentType}`);
          }
          if (error) {
            console.log(error.message);
            // consume response data to free up memory
            res.resume();
            return;
          }

          res.setEncoding('utf8');
          let rawData = '';
          res.on('data', (chunk) => rawData += chunk);
          res.on('end', () => {
            try {
                let parsedData = JSON.parse(rawData);
                user_name = parsedData.name;
                user_email = parsedData.email;
                console.log(user_name);
                console.log(user_email);
                alexa.registerHandlers(handlers);
                alexa.execute();
            } catch (e) {
              console.log(e.message);
            }
          });
        }).on('error', (e) => {
          console.log(`Got error: ${e.message}`);
        });
    }
};
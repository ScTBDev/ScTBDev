on *:NOTICE:*This nickname is registered and protected*:*:{
 if ($nick == NickServ && $network == ScT) {
   .msg nickserv IDENTIFY {IRC_PASSWORD}
 }
}

on *:NOTICE:*Password accepted*:*:{
 if ($nick == NickServ && $network == ScT) {
   join #scenetorrents
 }
}

raw 473:*:{
 if ($2 == #scenetorrents) {
   .msg SceneTorrents invite {USER_NAME} {IRC_KEY}
 }
}

on *:INVITE:#:{
 if ($nick == SceneTorrents) {
   join $chan
 }
}

fa_downloader
=============

furaffinity.net images downloader

For using this script, you need extract FA cookies from browser and put it in file cookies.txt in script folder.
For example, viewing cookies in chrome: https://developers.google.com/web/tools/chrome-devtools/storage/cookies
File format:

# Netscape HTTP Cookie File
# https://curl.haxx.se/docs/http-cookies.html
# This file was generated by libcurl! Edit at your own risk.

#HttpOnly_.furaffinity.net	TRUE	/	FALSE	2147483641	b	<b val>
#HttpOnly_.furaffinity.net	TRUE	/	FALSE	2147483641	sz	<sz val>
#HttpOnly_.furaffinity.net	TRUE	/	FALSE	2147483641	cc	1
#HttpOnly_.furaffinity.net	TRUE	/	TRUE	1607104462	__cfduid	<__cfduid val>
#HttpOnly_.furaffinity.net	TRUE	/	FALSE	2147483641	a	<a val>


Time 2147483641 = Tue Jan 19 06:14:01 AM MSK 2038

This mean, cookie be valid until Jan 2038.
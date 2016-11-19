{
   "UIconfig":[
      {
         "name":"menu",
         "type":"menu",
         "label":"Menu",
         "description":"Please choose the Mobile Menu for the site"
      },
      
      {
          "name":"logoimage",
          "type":"imagelist",
          "label":"Select a Logo Image",
          "description":"(Optional) An image to use for your logo, from the images directory.",
          "exclude": "",
          "directory": "images",
          "default":""
       },

      
      {
          "name":"logoheight",
          "type":"text",
          "label":"Logo Height",
          "description":"(Optional) Height of logo in pixels. Leave blank if you don't know and don't need the browser to resize.",
          "default":""
       },
       {
           "name":"logowidth",
           "type":"text",
           "label":"Logo width",
           "description":"(Optional) Width of logo in pixels. Leave blank if you don't know and don't need the browser to resize.",
           "default":""
        },


      {
         "name":"bizname",
         "type":"text",
         "label":"Business Name",
         "description":"Enter the name of your business",
         "default":""
      },
      {
          "name":"tagline",
          "type":"text",
          "label":"Tagline",
          "description":"Enter a tagline",
          "default":""
      },      
      {
         "name":"bizphone",
         "type":"text",
         "label":"Business Phone",
         "description":"Enter your phone number.",
         "default":""
      },
      {
         "name":"clicktocall",
         "type":"radio",
         "label":"Click To Call",
         "default":"off",
         "options":[
            "off: Disable",
            "text:Text",
            "image:Image"
         ]
      },
      {
         "name":"address1",
         "type":"text",
         "label":"Address 1",
         "description":"Enter your address.",
         "default":""
      },
      {
         "name":"address2",
         "type":"text",
         "label":"Address 2",
         "description":"Enter your address.",
         "default":""
      },
      {
         "name":"analyticsid",
         "type":"text",
         "label":"Google Analytics ID",
         "description":"Enter your Google Analytics ID (UA-xxxxxx-x)",
         "default":""
      },
      {
         "name":"stylenumber",
         "type":"list",
         "default":"0",
         "label":"Style",
         "options":[
            "0:Style 0",
            "1:Style 1",
            "2:Style 2",
            "3:Style 3",
            "4:Style 4",
            "5:Style 5",
            "6:Style 6"

         ]
      },
      {
         "name":"icons",
         "type":"radio",
         "label":"Use Icons",
         "default":"off",
         "options":[
            "on: Yes",
            "off: No"
         ]
      },
      {
          "name":"facebook",
          "type":"text",
          "label":"Facebook Name",
          "description":"Enter your facebook id. Routes to http://m.facebook.com/userid",
          "default":""
       },
       {
           "name":"twitter",
           "type":"text",
           "label":"Twitter Name",
           "description":"Enter your twitter id. Routes to http://mobile.twitter.com/userid",
           "default":""
        },
        {
            "name":"linkedin",
            "type":"text",
            "label":"Linked in url",
            "description":"Enter your linkedin id. Routes to http://www.linked.in.com/in/userid",
            "default":""
         }
   ],

   "iconField": {
	      "name": "icon",
	      "label": "Icon",
	      "type": "list",
	      "required": "false",
	      "description": "Which icon would you like to use?",
	      "options": {"none:None", "about:Information", "gift:Gift", "hours:Business Hours", "services:Services", 
	                          "rss:News", "reviews:Reviews", "events:Events", "faqs:FAQ", "directions:Directions", "coupons:Coupon",
	                          "images:Images", "videos:Videos", "menu:Menu", "home:Home", "phone:Phone", "facebook:Facebook", "twitter:Twitter", "linkedin:LinkedIn"}
	          
   }

}

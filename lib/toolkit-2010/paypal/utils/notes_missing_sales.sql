# Code Snippet to Alter members Table
# InnoDB doesn't like an ID column of zero

update `members` set id=100000 where id=0;
ALTER TABLE `members` ADD `force_pdt` TINYINT DEFAULT '0' NOT NULL ;
update `members` set id=0 where id=100000;

# Force PDT
# IF this is a non-zero value, we will turn the entire _REQUEST into _GET paramaters
# On the Thank You Page


###
Find missing sales
###

SELECT payer_email FROM `purchase_ipn` where item_number != 1057 AND custom like '1|%'

SELECT *  FROM `transactions` WHERE `seller_id` = 239 
AND `aff_id` = 0 
AND `product_id` = 1057
AND email in (
'banking@tarabyte.net',
'dashenman@earthlink.net',
'support@surefirewealth.com',
'kencomo88@aol.com',
'leonv@3i.co.za',
'dave@davidlantz.com',
'susankay@bdspros.com',
'philip.mcasey@sympatico.ca',
'bhmod@msn.com',
'MSNaturally@aol.com',
'dpcannam@hotmail.com',
'briangroundsell@btinternet.com',
'alex@harmony-dreams.com',
'garybrownlee@earthlink.net',
'RMFYI@YAHOO.COM',
'patrick_oh@pobox.com',
'lavridge@bigpond.net.au',
'onlineincome@sympatico.ca',
'oda@futurewill.com',
'skyerenterprises@nyc.rr.com',
'doston@comcast.net',
'tclark1@se.rr.com',
'ynader@gotadsl.co.uk',
'cal@amalg.com',
'dlw@1001plus.com',
'katc@charter.net',
'actualizeyourgoals@hotmail.com',
'alansimons@mac.com',
'epayment@itsbigitscool.com',
'ap@tnrglobal.com',
'rdh41@insightbb.com',
'apreston@mbscoaching.com',
'bruce@homeprograms.com',
'joannabstrauss@post.harvard.edu',
'romadonovan@sbcglobal.net',
'sales@infogoldrush.com',
'processor@supercivilisation.net',
'bobklyn@centurytel.net',
'sverret@sbcglobal.net'
);

SELECT *
FROM `transactions`
WHERE `seller_id` =239
AND `date_created` = '20050817'
AND `product_id` =1057
ORDER BY `time_created` ASC
LIMIT 0 , 300

SELECT *  FROM `transactions` WHERE `seller_id` = 239 AND `aff_id` = 0 AND `amount` < 90 AND  amount > 5 and `tr_status` != 'ok' AND `product_id` = 1057





SELECT *  FROM `transactions` WHERE `seller_id` = 239 
AND `aff_id` = 0 
AND `product_id` = 1057
AND email in (
'banking@tarabyte.net',
'dashenman@earthlink.net',
'support@surefirewealth.com',
'kencomo88@aol.com',
'leonv@3i.co.za',
'dave@davidlantz.com',
'susankay@bdspros.com',
'philip.mcasey@sympatico.ca',
'bhmod@msn.com',
'MSNaturally@aol.com',
'dpcannam@hotmail.com',
'briangroundsell@btinternet.com',
'alex@harmony-dreams.com',
'garybrownlee@earthlink.net',
'RMFYI@YAHOO.COM',
'patrick_oh@pobox.com',
'lavridge@bigpond.net.au',
'onlineincome@sympatico.ca',
'oda@futurewill.com',
'skyerenterprises@nyc.rr.com',
'doston@comcast.net',
'tclark1@se.rr.com',
'ynader@gotadsl.co.uk',
'cal@amalg.com',
'dlw@1001plus.com',
'katc@charter.net',
'actualizeyourgoals@hotmail.com',
'alansimons@mac.com',
'epayment@itsbigitscool.com',
'ap@tnrglobal.com',
'rdh41@insightbb.com',
'apreston@mbscoaching.com',
'bruce@homeprograms.com',
'joannabstrauss@post.harvard.edu',
'romadonovan@sbcglobal.net',
'sales@infogoldrush.com',
'processor@supercivilisation.net',
'bobklyn@centurytel.net',
'sverret@sbcglobal.net'
) And amount > 10

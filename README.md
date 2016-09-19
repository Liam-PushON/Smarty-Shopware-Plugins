# Smarty-Shopware-Plugins
##get_article
The get_article function grants the ability to pull specific articles (items) from the shopware database for display on your page. It also allows the pulling of products that were most recently updatted ('new') or the highest selling products ('top')
Currently there are 3 ways to select an article:
###Top & New Products
The 'type' paramter allows you to grab the newest or highest selling items from your database, this can be combined with the 'index' parameter to grab specific items, i.e. the 3rd highest selling product;
```*{get_article type='top' index=2}*```
The 'type' parameter does not require the 'index' parameter howver without it, the function will only pull the topmost article from the database.
####Example Usage:
You may choose to use this in a loop to grab multiple products:
```
*{for $i=0 to 4}*
    *<img src="{get_article type='top' index=$i return='image'}">*
*{/for}*
```
The above code will get images for the top 5 selling products
###Via ID
The 'id' paramter can be used to grab a specific article using its id.
```*{get_article id=48}*```
This will bypass the need for the 'type' and 'index' parameters, if this is set they will be ignored
###Via Name
The 'name' parameter can be used to grab a specific article using its name.
```*{get_article name='foobar'}*```
##Returned item
The 'return' parameter controls what is returned by the funcion allowing different strings to be pulled.
###name
Setting return to 'name' will return the articles name.
###link
Setting return to 'link' will return the url to the articles product page
###image
Setting return to 'image' will return the src url for the articles thumbnail image

##make_banner
**DEPENDS ON SLICK JS**
The make_banner function creates a product carousel banner using [slick js](http://kenwheeler.github.io/slick/)
###Parameters
The 'type' parameter allows the creation of two diffeent banners 'top, or 'new'. 'top' creates a banner listing top selling products. 'new' creates a banner listing the most recently updated and new products.
The 'amount' parameter allows you to choose how many proucts will appear in the carousel.

##Examples
```
*{make_banner type='top' amount=5}*
*{make_banner type='new' amount=6}*
*{get_article index=2 type='top' return='image'}*
*{get_article id=24 return='name'}*
*{get_article name='Cat' return='link'}*
```

##Valid Paramter Values
###get_article
####type
```
type='top'
type='new'
```
Note that this is converted into a numeric value so:
```type=0 can be used in place of type='top'```
AND
```type=1 can be used in place of type='new'```
####return
```
return='name'
return='link'
return='image'
```
Note that this is converted into a numeric value so:
```return=0 can be used in place of return='name'```
AND
```return=1 can be used in place of return='link'```
AND
```return=2 can be used in place of return='image'```
####index
An integer greaater than 0 (> 0)
####id
The id of an article in the 's_articles' table.
####
The name of an article in the 's_articles' table.

###make_banner
####amount
An integer greaater than 0 (> 0)
####type
```
type='top'
type='new'
```
Note that this is converted into a numeric value so:

```type=0 can be used in place of type='top'```

AND

```type=1 can be used in place of type='new'```



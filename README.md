# Page Templater - Embed your page


This is a [DokuWiki](http://www.dokuwiki.org) plugin. The News Page of [i-net software](https://www.inetsoftware.de/support/news) uses the blog plugin. The blog entries always have a plain look - unless you define some style for them, e.g. using boxes. Using Page Templater you can define templates for the blog namespaces. In our case, we have added a template for news entries which includes the archive.
You can also use the META plugin and define a template for only one page:

```
~~META:
templater page=:my:template-page
~~
```

## Template Prequisites


The template page has to have the word ``@@CONTENT@@`` in the content. This is the placeholder where the content of your original page will be located.

## Some more Template Functionality

You can define more placeholders on your own. Simply use the word ``@@YOURPLACEHOLDER@@`` in the template page (where ``YOURPLACEHOLDER`` is actually your word to be replaced).

Template Page Example:

```
====== This my page with a Placeholder ======
 
**@@YOURPLACEHOLDER@@**
```

You can now define the following in your page:

```
~~META:
templater page = :my:template
&templater yourplaceholder = This is an important text
&templater !yourimageholder = {{:this:can:be:an:image.jpg|}}
~~
```

**Note:** The page above defines a templater property using an exclamation mark (!) - this property will be fed into the DokuWiki XHTML renderer and produce rendered output.

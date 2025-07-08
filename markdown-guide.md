# Pepperminty Wiki Markdown Guide
Hello! This simple guide will get you up and running with Pepperminty Wiki's variant on Markdown in no time. Markdown can be found all over the Internet, so you may already be familiar with some of it. Sometimes, this logo is used when Markdown is supported:

![The official Markdown logo](https://commonmark.org/images/markdown-mark-apple-touch.png)

\* Markdown formatting on Pepperminty Wiki may be slightly different to that presented in this guide.

## Basic formatting
Text can be formatted in a variety of ways. Examples include the following. Markdown syntax you type into the page editor is shown first, and a preview thereof is shown directly beneath it.

```markdown
**bold text**
```
**bold text**

```markdown
*italics text*
```

*italics text*

```markdown
~~strikethrough text~~
```

~~strikethrough text~~

```markdown
> Blockquote
```

> Blockquote

```markdown
* Bullet pointed list
* Another item
* Yet another item
```

* Bullet pointed list
* Another item
* Yet another item

```markdown
1. Ordered list
2. Item 2
3. Item 3
```

1. Ordered list
2. Item 2
3. Item 3


## Headings
Headings are denoted with 1 or more hash signs `#` **and a space** before the heading text:

```markdown
# Heading 1

## Heading 2

### Heading 3

#### Heading 4

##### Heading 5

###### Heading 6
```

# Heading 1

## Heading 2

### Heading 3

#### Heading 4

##### Heading 5

###### Heading 6

...since Pepperminty Wiki fills in the page name for you automatically, it is recommended you start any headings on your pages with **Heading 2** as the top-level, and **avoid using Heading 1**.

## Tables
Tables can be created like so:

```markdown
Column 1 | Column 2 | Column 3
---------|----------|---------
cell 1-1 | cell 1-2 | cell 1-3
cell 2-1 | cell 2-2 | cell 2-3
cell 3-1 | cell 3-2 | cell 3-3
```

Column 1 | Column 2 | Column 3
---------|----------|---------
cell 1-1 | cell 1-2 | cell 1-3
cell 2-1 | cell 2-2 | cell 2-3
cell 3-1 | cell 3-2 | cell 3-3

...It does not matter if the content of any cell overflows the space available:


```markdown
Column 1 | Column 2 | Column 3
---------|----------|---------
cell 1-1 | cell 1-2 | cell 1-3
cell 2-1 | cell 2-2 | cell 2-3
cell 3-1 | really really really really really long  | cell 3-3
```

Column 1 | Column 2 | Column 3
---------|----------|---------
cell 1-1 | cell 1-2 | cell 1-3
cell 2-1 | cell 2-2 | cell 2-3
cell 3-1 | really really really really really long | cell 3-3

## Pepperminty Wiki exclusive syntax
Pepperminty Wiki supports a number of additional features on top of base Markdown:

```markdown
==highlighted text==
```

<span style="background-color:yellow;">highlighted text</span>

```markdown
Some text^superscript^
```

Some text<sup>superscript</sup>

```markdown
Some text~superscript~
```

Some text<sub>superscript</sub>


### Internal links
Linking between different pages is easy. Simply surround the name of the page you want to link to in [[square brackets]]:

```markdown
[[Another page name]]
```

<a href="#this-is-just-a-test">Another page name</a>

### Images
Images on Pepperminty Wiki need to be first uploaded to your Pepperminty Wiki instance. Once you have uploaded the image to your Pepperminty Wiki instance, do this to display the image on a page:

```markdown
![Alt text](Files/Your image.png)
```

...this will cause the image to display in place of the above. Replace `Files/Your image.png` with the page name your image uploaded to, and `Alt text` with alternative text to display in case the image can't be loaded. The alt text is **very important**, as without it screen reader users will not be able to understand your image!

This syntax also works for **videos** and **audio** files too.

Images can also be manipulated by adding some extra tags to the above syntax. For example, we can limit the size of a displayed image like this:

```markdown
![Alt text](Files/Your image.png | 350x350)
```

With the above, Pepperminty Wiki will limit the size of your image to 350x350 pixels.

You can also float images to the left:


```markdown
![Alt text](Files/Your image.png | 350x350 | left)
```

...text will automatically flow around floated images.

Finally, you can also ask Pepperminty Wiki to render an image's alt text as a caption directly below it:

```markdown
![Alt text](Files/Your image.png | 350x350 | left | caption)
```

Any combination of these options for formatting images should be supported.

### Tables of contents
Insert the following alone on an empty line, with blank lines before and after to generate an automatic table of contents:

```markdown
[__TOC__]
```

...Pepperminty Wiki will read all the [headings](#headings) you have created on your page, and compiles them into a table of contents automatically and inserts wherever it sees this exact text.

## More help
All Pepperminty Wiki instances come with a comprehensive help page. It is, by default, accessible from the `Help` link at the bottom-right of every page. It contains a lot of useful advice on how to use Pepperminty Wiki and make the most of all the features it has.

The following resources are also helpful for mastering the basics of Markdown:

- [Learn Markdown in 60 seconds](https://commonmark.org/help/)
- [GitHub's Markdown Guide](https://github.com/adam-p/markdown-here/wiki/Markdown-Cheatsheet)
- [Mastering Markdown](https://www.gitbook.com/book/roachhd/master-markdown/details)
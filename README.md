# Unite US Coding Exercise

I had some fun with this, so it may be a touch over-engineered in places, but I feel like that's partly the point of these exercises.

I decided to build something a little more generic and allow it to be configured. That way it can be used in this specific situation, but also to handle similar situations (or this situation, when the spec inevitably changes).

I wrote a stupidly-simple template class to make this configuration possible, and hid the instantiation of it behind a factory method, so it can be subbed out for a more robust implementation if needed.

You'll need PHP installed locally to run it. From the command line, just navigate to the root directory of the project (the directory this file is in!) and run:

```
php demo.php
```

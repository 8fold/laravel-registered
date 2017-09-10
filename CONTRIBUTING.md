# Contributing to Registered

[8fold](https://8fold.pro) if very purpose and goal oriented. Therefore, when discussing the preferred way to contribute to our projects, we will use that type of language.

**Solutions over scapegoats:** Git has this concept of "blame". While we understand that comedy of the situation, it's important to note that no amount of blaming someone actually solves a problem. So, if these goals are not met each and every time, it's okay. The more important point is that we continue to strive to reach the goals (or change what the goals are).

**Easy to change in future:** This is the essence of agility according to [Pragmatic Dave Thomas](https://pragdave.me/blog/2014/03/04/time-to-kill-agile.html). Therefore, "when faced with two or more alternatives that deliver roughly the same value, [we will prefer] the path that makes future change easier."

**Type safe:** While PHP is not a strictly typed language, we will strive for 100% coverage with regard to type hinting the code.

**The right amount of testing:** Testing has become quite the talk as of late, and we tend to agree. Having said that, [testing for its own sake](https://youtu.be/a-BOSpxYJ9M) has also become prevalent. So, let's say you have Method X and all it does is returns the value from passing data to Method Y. Further, you have tested Method Y. There is very little need to test Method X at this juncture. If a defect is reported on Method Y; write the test based on the defect. Finally, if we are selecting stable packages that also have the right amount of testing (or even over-testing), there is no need to write tests that test the validity of those packages. While it's too late to say this, in short, automated tests do not deliver value to users and if we develop in a sane fashion (small things that are easy to change in the future), refactoring should not break that much.

**Avoid hard-coded string values:** To aid adoption and localization, we should strive to move displayed string falues to localization files.
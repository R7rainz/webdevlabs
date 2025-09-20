
function isValidISBN(isbn) {
  var cleanIsbn = isbn.replace(/[\s-]/g, '');

  var isbn10 = /^(?:\d{9}[\dXx])$/;
  var isbn13 = /^(?:97[89])\d{10}$/;

  return isbn10.test(cleanIsbn) || isbn13.test(cleanIsbn);
}

console.log(isValidISBN('0-306-40615-2'));     // true (ISBN-10)
console.log(isValidISBN('978-3-16-148410-0')); // true (ISBN-13)
console.log(isValidISBN('123456789X'));        // true (ISBN-10)
console.log(isValidISBN('1234567890123'));     // false

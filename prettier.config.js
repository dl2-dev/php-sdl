module.exports = {
  arrowParens: "always",
  overrides: [
    {
      files: "LICENSE",
      options: { parser: "markdown" },
    },
    {
      files: ["*.latte", "*.xml", "*.xml.dist"],
      options: {
        parser: "html",
        printWidth: 178,
      },
    },
    {
      files: ["*.php", "bin/console"],
      options: { parser: "php", tabWidth: 4, printWidth: 110 },
    },
  ],
  printWidth: 88,
  quoteProps: "consistent",
  tabWidth: 2,
  trailingComma: "all",
};

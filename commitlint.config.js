module.exports = {
  extends: ["@commitlint/config-conventional"],

  // prettier-ignore
  rules: {
    "subject-case": [
      2,
      "always",
      "sentence-case"
    ],
    "type-enum": [
      2,
      "always",
      [
        "chore",
        "ci",
        "docs",
        "feat",
        "fix",
        "perf",
        "refactor",
        "revert",
        "style",
        "test",
      ],
    ],
  },
};

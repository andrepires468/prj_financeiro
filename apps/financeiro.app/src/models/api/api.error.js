class APIError extends Error {
  constructor(message, data) {
    super(message);
    this.name = "APIError";
    this.data = data;
  }
}

export default APIError;
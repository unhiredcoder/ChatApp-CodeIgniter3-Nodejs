import express from 'express';
import cors from 'cors';
import mongoose from 'mongoose';
import bcrypt from "bcryptjs";

const app = express();

// Middleware
app.use(cors({
  origin: ["http://localhost", "http://127.0.0.1"],
  methods: ["GET", "POST"]
}));
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

// Connect to MongoDB
mongoose.connect("mongodb+srv://chatapp2:slash123@cluster0.q9veugy.mongodb.net/?appName=Cluster0/user", {
  useNewUrlParser: true,
  useUnifiedTopology: true
}).then(() => console.log("MongoDB connected"))
  .catch(err => console.error(err));

// Define schema
const userSchema = new mongoose.Schema({
  username: { type: String, required: true },
  password: { type: String, required: true }
});

// Create model
const User = mongoose.model("User", userSchema);

// Route
app.post("/api/register", async (req, res) => {
  try {
    console.log(req.body)
    const { username, password } = req.body;
    const hashedPassword = bcrypt.hashSync(password, 10);
    const newUser = new User({ username, 'password':hashedPassword });
    await newUser.save();
    res.send("User saved successfully!");
  } catch (err) {
    console.error(err);
    res.status(500).send("Error saving user");
  }
})

app.post("/api/login", async (req, res) => {
  try {
    console.log(req.body);
    const { username, password } = req.body;

    // Validate input
    if (!username || !password) {
      return res.status(400).send({ error: "Username and password are required" });
    }

    // Find user
    const existingUser = await User.findOne({ username });
    if (!existingUser) {
      return res.status(404).send({ error: "User doesn't exist!" });
    }

    // Compare password (assuming it's hashed in DB)
    const validPass = await bcrypt.compare(password, existingUser.password);
    if (!validPass) {
      return res.status(401).send({ error: "Incorrect password" });
    }

    // Success
    res.send({ success: "Logged in successfully" });
  } catch (err) {
    console.error(err);
    res.status(500).send("Internal Server Error");
  }
});


// Start server
app.listen(5555, () => {
  console.log("Server running at http://localhost:5555");
});

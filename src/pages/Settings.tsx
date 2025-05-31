import { useForm } from 'react-hook-form';
import { Box, Card, CardContent, TextField, Button, Typography, MenuItem, Grid } from '@mui/material';
import { useSettingsStore } from '../store/settings';

const models = [
  { value: 'anthropic/claude-2', label: 'Claude 2' },
  { value: 'google/palm-2-chat-bison', label: 'PaLM 2 Chat' },
  { value: 'meta-llama/llama-2-70b-chat', label: 'Llama 2 70B' },
  { value: 'openai/gpt-3.5-turbo', label: 'GPT-3.5 Turbo' },
  { value: 'openai/gpt-4', label: 'GPT-4' },
];

const tones = [
  { value: 'professional', label: 'Professional' },
  { value: 'casual', label: 'Casual' },
  { value: 'academic', label: 'Academic' },
  { value: 'journalistic', label: 'Journalistic' },
  { value: 'creative', label: 'Creative' },
];

export function Settings() {
  const settings = useSettingsStore();
  const { register, handleSubmit } = useForm({
    defaultValues: settings,
  });

  const onSubmit = (data: any) => {
    settings.updateSettings(data);
  };

  return (
    <Box>
      <Typography variant="h4" gutterBottom>
        Settings
      </Typography>

      <form onSubmit={handleSubmit(onSubmit)}>
        <Grid container spacing={3}>
          <Grid item xs={12} md={6}>
            <Card>
              <CardContent>
                <Typography variant="h6" gutterBottom>
                  WordPress Connection
                </Typography>
                <TextField
                  {...register('wordpressUrl')}
                  label="WordPress Site URL"
                  fullWidth
                  margin="normal"
                />
                <TextField
                  {...register('wordpressUsername')}
                  label="Username"
                  fullWidth
                  margin="normal"
                />
                <TextField
                  {...register('wordpressPassword')}
                  label="Password"
                  type="password"
                  fullWidth
                  margin="normal"
                />
              </CardContent>
            </Card>
          </Grid>

          <Grid item xs={12} md={6}>
            <Card>
              <CardContent>
                <Typography variant="h6" gutterBottom>
                  OpenRouter Configuration
                </Typography>
                <TextField
                  {...register('openrouterApiKey')}
                  label="API Key"
                  fullWidth
                  margin="normal"
                />
                <TextField
                  {...register('selectedModel')}
                  select
                  label="AI Model"
                  fullWidth
                  margin="normal"
                >
                  {models.map((model) => (
                    <MenuItem key={model.value} value={model.value}>
                      {model.label}
                    </MenuItem>
                  ))}
                </TextField>
                <TextField
                  {...register('rewriteTone')}
                  select
                  label="Rewriting Tone"
                  fullWidth
                  margin="normal"
                >
                  {tones.map((tone) => (
                    <MenuItem key={tone.value} value={tone.value}>
                      {tone.label}
                    </MenuItem>
                  ))}
                </TextField>
              </CardContent>
            </Card>
          </Grid>

          <Grid item xs={12}>
            <Card>
              <CardContent>
                <Typography variant="h6" gutterBottom>
                  Import Settings
                </Typography>
                <TextField
                  {...register('importFrequency')}
                  select
                  label="Import Frequency"
                  fullWidth
                  margin="normal"
                >
                  <MenuItem value="hourly">Hourly</MenuItem>
                  <MenuItem value="daily">Daily</MenuItem>
                  <MenuItem value="weekly">Weekly</MenuItem>
                </TextField>
                <TextField
                  {...register('maxPostsPerImport')}
                  label="Max Posts per Import"
                  type="number"
                  fullWidth
                  margin="normal"
                  InputProps={{ inputProps: { min: 1, max: 100 } }}
                />
              </CardContent>
            </Card>
          </Grid>
        </Grid>

        <Box sx={{ mt: 3 }}>
          <Button type="submit" variant="contained" color="primary" size="large">
            Save Settings
          </Button>
        </Box>
      </form>
    </Box>
  );
}
import { useState } from 'react';
import Papa from 'papaparse';

interface CSVFeed {
  Categoria: string;
  Nome_Fonte: string;
  URL_Feed_RSS: string;
  Descrizione: string;
  Frequenza_Aggiornamento: string;
  Tipo_Contenuto: string;
  Qualità: string;
}

interface Props {
  onImport: (feeds: any[]) => void;
}

export function CSVImport({ onImport }: Props) {
  const [error, setError] = useState<string>('');
  const [importing, setImporting] = useState(false);

  const handleFileUpload = (event: React.ChangeEvent<HTMLInputElement>) => {
    const file = event.target.files?.[0];
    if (!file) return;

    setImporting(true);
    setError('');

    Papa.parse(file, {
      header: true,
      complete: (results) => {
        setImporting(false);
        
        if (results.errors.length > 0) {
          setError('Error parsing CSV file');
          return;
        }

        const feeds = (results.data as CSVFeed[]).map(row => ({
          categoria_feed: row.Categoria,
          nome_fonte: row.Nome_Fonte,
          url_rss: row.URL_Feed_RSS,
          descrizione_feed: row.Descrizione,
          frequenza: mapFrequency(row.Frequenza_Aggiornamento),
          tipo_contenuto: row.Tipo_Contenuto,
          priorita: mapQuality(row.Qualità),
          status: 'active'
        }));

        onImport(feeds);
      },
      error: (error) => {
        setImporting(false);
        setError(error.message);
      }
    });
  };

  const mapFrequency = (freq: string): string => {
    const map: { [key: string]: string } = {
      'oraria': 'hourly',
      'giornaliera': 'daily',
      'settimanale': 'weekly'
    };
    return map[freq.toLowerCase()] || 'daily';
  };

  const mapQuality = (quality: string): number => {
    const map: { [key: string]: number } = {
      'alta': 3,
      'media': 2,
      'bassa': 1
    };
    return map[quality.toLowerCase()] || 2;
  };

  return (
    <div className="csv-import">
      <div className="form-group">
        <label className="form-label">Import Feeds from CSV</label>
        <input
          type="file"
          accept=".csv"
          onChange={handleFileUpload}
          className="form-input"
          disabled={importing}
        />
        <p className="form-help">
          Upload a CSV file with the following columns: Categoria, Nome_Fonte, URL_Feed_RSS, 
          Descrizione, Frequenza_Aggiornamento, Tipo_Contenuto, Qualità
        </p>
        {error && <p className="error-message">{error}</p>}
        {importing && <p>Importing feeds...</p>}
      </div>
    </div>
  );
}
<section class="ticket">
    <div class="ticket-paper">
        <div class="ticket-header"><strong>LIBRAIRIE</strong><span>Gestion des ventes</span></div>
        <p><strong>N° vente :</strong> {{ $vente->numero_vente }}</p>
        <p><strong>Date :</strong> {{ $vente->date_vente->format('d/m/Y H:i') }}</p>
        <p><strong>Employé :</strong> {{ $vente->user->name }}</p>
        <div class="table-wrap"><table><thead><tr><th>Produit</th><th>Qté</th><th>Prix</th><th>Total</th></tr></thead><tbody>
            @foreach ($vente->details as $detail)
                <tr><td>{{ $detail->product->nom }}</td><td>{{ $detail->quantite }}</td><td>{{ number_format((float) $detail->prix, 2, ',', ' ') }} DH</td><td>{{ number_format((float) $detail->sous_total, 2, ',', ' ') }} DH</td></tr>
            @endforeach
        </tbody></table></div>
        <div class="ticket-total"><strong>TOTAL</strong><strong>{{ number_format((float) $vente->total, 2, ',', ' ') }} DH</strong></div>
        <p class="ticket-thanks">Merci pour votre achat.</p>
        <button class="button button-primary no-print" type="button" onclick="window.print()">Imprimer le ticket</button>
    </div>
</section>
